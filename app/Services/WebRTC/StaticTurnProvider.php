<?php

namespace App\Services\WebRTC;

class StaticTurnProvider implements TurnProviderInterface
{
    /**
     * Get ICE servers configuration array from static config.
     *
     * @return array
     */
    public function getIceServers(): array
    {
        if (! $this->isAvailable()) {
            return [];
        }

        $server = [
            'urls' => config('webrtc.static.url'),
        ];

        if ($username = config('webrtc.static.username')) {
            $server['username'] = $username;
        }

        if ($credential = config('webrtc.static.credential')) {
            $server['credential'] = $credential;
        }

        return [$server];
    }

    /**
     * Check if static TURN configuration is present.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return ! empty(config('webrtc.static.url'));
    }

    /**
     * Refresh static credentials (no-op for static provider).
     *
     * @param bool $force
     * @return array
     */
    public function refreshCredentials(bool $force = false): array
    {
        return $this->getIceServers();
    }
}
