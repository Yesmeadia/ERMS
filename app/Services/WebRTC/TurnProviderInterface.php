<?php

namespace App\Services\WebRTC;

interface TurnProviderInterface
{
    /**
     * Get ICE servers configuration array provided by this TURN provider.
     *
     * @return array Array of ICE server configurations compatible with RTCPeerConnection.
     */
    public function getIceServers(): array;

    /**
     * Check if this TURN provider is configured and available.
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Refresh or rotate credentials with the TURN provider.
     *
     * @param bool $force Force refresh even if cached credentials are still valid.
     * @return array The refreshed ICE servers array.
     */
    public function refreshCredentials(bool $force = false): array;
}
