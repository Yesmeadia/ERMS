<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'cloudflare' => [
        'turnstile_site_key' => env('CLOUDFLARE_TURNSTILE_SITE_KEY'),
        'turnstile_secret_key' => env('CLOUDFLARE_TURNSTILE_SECRET_KEY'),
    ],

    'cashfree' => [
        'client_id'      => env('CASHFREE_CLIENT_ID'),
        'client_secret'  => env('CASHFREE_CLIENT_SECRET'),
        'env'            => env('CASHFREE_ENV', 'sandbox'), // 'sandbox' or 'production'
        'webhook_secret' => env('CASHFREE_WEBHOOK_SECRET'), // Used to verify Cashfree webhook signatures
    ],

];
