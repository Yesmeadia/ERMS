<?php

namespace App\Services\WebRTC;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MeteredTurnProvider implements TurnProviderInterface
{
    /**
     * Get ICE servers configuration array provided by Metered TURN.
     * Uses cached active credential to avoid the ~2 minute propagation delay.
     *
     * @return array
     */
    public function getIceServers(): array
    {
        if (! $this->isAvailable()) {
            return [];
        }

        $cacheKey = config('webrtc.cache.key', 'webrtc:metered:active-credential');
        $cached = Cache::get($cacheKey);

        $buffer = (int) config('webrtc.cache.rotation_buffer_seconds', 1800);
        $now = now()->timestamp;

        // If we have valid cached credentials that are not nearing expiration, reuse them
        if (is_array($cached) && ! empty($cached['ice_servers']) && isset($cached['expires_at'])) {
            if (($cached['expires_at'] - $now) > $buffer) {
                return $cached['ice_servers'];
            }
        }

        // Cache is empty, expired, or within rotation buffer — refresh credentials
        return $this->refreshCredentials(false);
    }

    /**
     * Check if Metered TURN is enabled and has required credentials configured.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        $enabled = (bool) config('webrtc.metered.enabled', false);
        $domain = config('webrtc.metered.domain');
        $hasKey = ! empty(config('webrtc.metered.secret_key')) || ! empty(config('webrtc.metered.project_api_key'));

        return $enabled && ! empty($domain) && $hasKey;
    }

    /**
     * Refresh credentials with Metered API and cache the active result.
     * Supports both GET /credentials?apiKey=... and POST /credential?secretKey=...
     *
     * @param bool $force Force refresh regardless of current cache state.
     * @return array
     */
    public function refreshCredentials(bool $force = false): array
    {
        $cacheKey = config('webrtc.cache.key', 'webrtc:metered:active-credential');
        $cached = Cache::get($cacheKey);

        // If not forced and existing cached credentials are still very fresh (> 30 mins remaining), reuse them
        if (! $force && is_array($cached) && ! empty($cached['ice_servers']) && isset($cached['expires_at'])) {
            $remaining = $cached['expires_at'] - now()->timestamp;
            $buffer = (int) config('webrtc.cache.rotation_buffer_seconds', 1800);
            if ($remaining > $buffer) {
                return $cached['ice_servers'];
            }
        }

        $domain = $this->getCleanDomain();
        $secretKey = config('webrtc.metered.secret_key');
        $apiKey = config('webrtc.metered.project_api_key');
        $effectiveKey = $apiKey ?: $secretKey;
        $expirySeconds = (int) config('webrtc.metered.credential_expiry', 172800);

        // Method 1: GET /api/v1/turn/credentials?apiKey={effectiveKey}
        // Note: Metered's GET credentials endpoint specifically requires the 'apiKey' query parameter
        try {
            $getUrl = "https://{$domain}/api/v1/turn/credentials";
            $response = Http::timeout(6)
                ->acceptJson()
                ->get($getUrl, ['apiKey' => $effectiveKey]);

            if ($response->successful()) {
                $rawServers = $response->json();

                if (is_array($rawServers)) {
                    $sanitizedServers = $this->sanitizeIceServers($rawServers);

                    if (! empty($sanitizedServers)) {
                        $this->cacheCredentials($cacheKey, $sanitizedServers, $expirySeconds);

                        Log::info('[Metered TURN] Active credentials fetched via GET /credentials.', [
                            'count' => count($sanitizedServers),
                        ]);

                        return $sanitizedServers;
                    }
                }
            } else {
                Log::warning('[Metered TURN] GET /credentials returned non-200.', [
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[Metered TURN] Exception during GET /credentials.', [
                'error' => $e->getMessage(),
            ]);
        }

        // Method 2: POST /api/v1/turn/credential?secretKey={secretKey}
        // If GET failed and a secret key is available, generate/rotate credentials via Metered REST API
        if (! empty($secretKey)) {
            try {
                $postUrl = "https://{$domain}/api/v1/turn/credential?secretKey=" . urlencode($secretKey);
                $postResponse = Http::timeout(6)
                    ->acceptJson()
                    ->post($postUrl, [
                        'label' => config('webrtc.metered.credential_label', 'erms-production'),
                        'expiryInSeconds' => $expirySeconds,
                    ]);

                if ($postResponse->successful()) {
                    $data = $postResponse->json();

                    // If an apiKey is returned with the new credential, retrieve the full ICE servers array
                    if (! empty($data['apiKey'])) {
                        $credResponse = Http::timeout(6)
                            ->acceptJson()
                            ->get("https://{$domain}/api/v1/turn/credentials", ['apiKey' => $data['apiKey']]);

                        if ($credResponse->successful() && is_array($credResponse->json())) {
                            $sanitizedServers = $this->sanitizeIceServers($credResponse->json());
                            if (! empty($sanitizedServers)) {
                                $this->cacheCredentials($cacheKey, $sanitizedServers, $expirySeconds);
                                return $sanitizedServers;
                            }
                        }
                    }

                    // Alternatively build standard ICE servers directly from returned username & password
                    if (! empty($data['username']) && ! empty($data['password'])) {
                        $servers = $this->buildIceServersFromCredentials($data['username'], $data['password']);
                        $this->cacheCredentials($cacheKey, $servers, $expirySeconds);

                        Log::info('[Metered TURN] Credentials generated via POST /credential.', [
                            'username' => $data['username'],
                        ]);

                        return $servers;
                    }
                } else {
                    Log::warning('[Metered TURN] POST /credential returned non-200.', [
                        'status' => $postResponse->status(),
                        'body' => $postResponse->json() ?? $postResponse->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('[Metered TURN] Exception during POST /credential.', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Graceful fallback: return stale cached credentials if available
        if (is_array($cached) && ! empty($cached['ice_servers'])) {
            Log::warning('[Metered TURN] Using existing cached credentials as fallback.');
            return $cached['ice_servers'];
        }

        return [];
    }

    /**
     * Cache the active credentials with metadata.
     *
     * @param string $cacheKey
     * @param array $servers
     * @param int $expirySeconds
     */
    protected function cacheCredentials(string $cacheKey, array $servers, int $expirySeconds): void
    {
        $cacheData = [
            'ice_servers' => $servers,
            'created_at' => now()->timestamp,
            'expires_at' => now()->timestamp + $expirySeconds,
        ];

        Cache::put($cacheKey, $cacheData, now()->addSeconds($expirySeconds));
    }

    /**
     * Clean and normalize domain name.
     *
     * @return string
     */
    protected function getCleanDomain(): string
    {
        $domain = trim((string) config('webrtc.metered.domain'));

        // Remove protocol if present
        $domain = preg_replace('#^https?://#i', '', $domain);

        // Remove any trailing slashes or paths
        $domain = explode('/', $domain)[0];

        // If user entered only subdomain, append .metered.live
        if (! str_contains($domain, '.')) {
            $domain .= '.metered.live';
        }

        return $domain;
    }

    /**
     * Sanitize and format the ICE servers array returned by Metered.
     * Ensures only valid WebRTC RTCIceServer objects are passed to browsers.
     *
     * @param array $servers
     * @return array
     */
    protected function sanitizeIceServers(array $servers): array
    {
        $valid = [];

        foreach ($servers as $server) {
            if (! is_array($server) || empty($server['urls'])) {
                continue;
            }

            $entry = [
                'urls' => $server['urls'],
            ];

            if (! empty($server['username'])) {
                $entry['username'] = (string) $server['username'];
            }

            if (! empty($server['credential'])) {
                $entry['credential'] = (string) $server['credential'];
            }

            $valid[] = $entry;
        }

        return $valid;
    }

    /**
     * Build standard Metered ICE servers from generated username & password.
     *
     * @param string $username
     * @param string $password
     * @return array
     */
    protected function buildIceServersFromCredentials(string $username, string $password): array
    {
        return [
            ['urls' => 'stun:stun.relay.metered.ca:80'],
            [
                'urls' => 'turn:global.relay.metered.ca:80',
                'username' => $username,
                'credential' => $password,
            ],
            [
                'urls' => 'turn:global.relay.metered.ca:80?transport=tcp',
                'username' => $username,
                'credential' => $password,
            ],
            [
                'urls' => 'turn:global.relay.metered.ca:443',
                'username' => $username,
                'credential' => $password,
            ],
            [
                'urls' => 'turns:global.relay.metered.ca:443?transport=tcp',
                'username' => $username,
                'credential' => $password,
            ],
        ];
    }
}
