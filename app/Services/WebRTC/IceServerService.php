<?php

namespace App\Services\WebRTC;

class IceServerService
{
    /**
     * @var MeteredTurnProvider
     */
    protected MeteredTurnProvider $meteredProvider;

    /**
     * @var StaticTurnProvider
     */
    protected StaticTurnProvider $staticProvider;

    public function __construct(
        MeteredTurnProvider $meteredProvider,
        StaticTurnProvider $staticProvider
    ) {
        $this->meteredProvider = $meteredProvider;
        $this->staticProvider = $staticProvider;
    }

    /**
     * Get the complete ICE servers configuration for RTCPeerConnection.
     * Guaranteed consistent for both Student and Admin browsers.
     * Order: STUN first (direct P2P), followed by TURN relay fallback.
     *
     * @return array
     */
    public function getIceServers(): array
    {
        $iceServers = [];

        // 1. Primary STUN Configuration (Direct P2P traversal)
        if (config('webrtc.stun.enabled', true)) {
            $stunUrls = config('webrtc.stun.urls', [
                'stun:stun.l.google.com:19302',
                'stun:stun1.l.google.com:19302',
            ]);

            if (! empty($stunUrls)) {
                // If it's an array of strings, add individual or combined entry
                foreach ((array) $stunUrls as $url) {
                    if (! empty($url)) {
                        $iceServers[] = [
                            'urls' => $url,
                        ];
                    }
                }
            }
        }

        // 2. TURN Relay Fallback (Relay for symmetric NAT / restrictive firewalls)
        if (config('webrtc.turn.enabled', false)) {
            $turnProvider = $this->resolveTurnProvider();

            if ($turnProvider && $turnProvider->isAvailable()) {
                $turnServers = $turnProvider->getIceServers();
                foreach ($turnServers as $server) {
                    $iceServers[] = $server;
                }
            }
        }

        // Fallback safety guard: always ensure at least one STUN server exists
        if (empty($iceServers)) {
            $iceServers[] = [
                'urls' => 'stun:stun.l.google.com:19302',
            ];
        }

        return $iceServers;
    }

    /**
     * Resolve the active TURN provider based on configuration.
     *
     * @return TurnProviderInterface|null
     */
    public function resolveTurnProvider(): ?TurnProviderInterface
    {
        $providerName = config('webrtc.turn.provider', 'metered');

        if ($providerName === 'metered' || config('webrtc.metered.enabled', false)) {
            if ($this->meteredProvider->isAvailable()) {
                return $this->meteredProvider;
            }
        }

        if ($providerName === 'static' || $this->staticProvider->isAvailable()) {
            return $this->staticProvider;
        }

        return null;
    }

    /**
     * Force warm-up or refresh of TURN credentials.
     *
     * @param bool $force
     * @return array
     */
    public function warmupTurnCredentials(bool $force = false): array
    {
        $provider = $this->resolveTurnProvider();

        if ($provider) {
            return $provider->refreshCredentials($force);
        }

        return [];
    }
}
