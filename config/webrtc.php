<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WebRTC Master Switch
    |--------------------------------------------------------------------------
    |
    | Enables or disables WebRTC live camera monitoring and proctoring.
    |
    */

    'enabled' => (bool) env('WEBRTC_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | STUN Configuration
    |--------------------------------------------------------------------------
    |
    | STUN (Session Traversal Utilities for NAT) allows direct peer-to-peer
    | audio/video connections when clients are behind non-symmetric NAT.
    | STUN is attempted first before falling back to TURN relay.
    |
    */

    'stun' => [
        'enabled' => (bool) env('WEBRTC_STUN_ENABLED', true),
        'urls' => [
            env('WEBRTC_STUN_URL', 'stun:stun.l.google.com:19302'),
            'stun:stun1.l.google.com:19302',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | TURN Relay Configuration
    |--------------------------------------------------------------------------
    |
    | TURN (Traversal Using Relays around NAT) serves as a relay fallback
    | when symmetric NATs or strict institutional/cellular firewalls block
    | direct STUN P2P connections.
    | Supported providers: 'metered', 'static'
    |
    */

    'turn' => [
        'enabled' => (bool) env('WEBRTC_TURN_ENABLED', false),
        'provider' => env('WEBRTC_TURN_PROVIDER', 'metered'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Metered TURN Provider Settings
    |--------------------------------------------------------------------------
    |
    | Managed TURN service by Metered Video (https://www.metered.ca).
    | Provides geo-distributed TURN over UDP, TCP, and TLS (port 443).
    | Newly created credentials take up to ~2 minutes to propagate.
    |
    */

    'metered' => [
        'enabled' => (bool) env('METERED_ENABLED', false),
        'domain' => env('METERED_DOMAIN'), // e.g. 'myaccount' or 'myaccount.metered.live'
        'secret_key' => env('METERED_SECRET_KEY'),
        'project_id' => env('METERED_PROJECT_ID'),
        'project_api_key' => env('METERED_PROJECT_API_KEY'),
        'credential_label' => env('METERED_CREDENTIAL_LABEL', 'erms-production'),
        'credential_expiry' => (int) env('METERED_CREDENTIAL_EXPIRY', 172800), // 48 hours in seconds
        'region' => env('METERED_REGION', 'global'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Static / Local TURN Fallback
    |--------------------------------------------------------------------------
    |
    | Fallback static TURN server (e.g. self-hosted coturn or test credentials).
    |
    */

    'static' => [
        'url' => env('STATIC_TURN_URL', env('TURN_URL')),
        'username' => env('STATIC_TURN_USERNAME', env('TURN_USERNAME')),
        'credential' => env('STATIC_TURN_PASSWORD', env('TURN_PASSWORD')),
    ],

    /*
    |--------------------------------------------------------------------------
    | TURN Credential Caching & Rotation
    |--------------------------------------------------------------------------
    |
    | Cache configuration for maintaining active TURN credentials to avoid
    | race conditions from propagation delays.
    |
    */

    'cache' => [
        'key' => 'webrtc:metered:active-credential',
        'rotation_buffer_seconds' => (int) env('WEBRTC_ROTATION_BUFFER_SECONDS', 1800), // Rotate 30 mins before expiry
    ],

];
