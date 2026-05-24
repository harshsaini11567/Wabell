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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'hyperpay' => [
        'access_token' => env('HYPERPAY_ACCESS_TOKEN'),
        'entity_id_visa' => env('HYPERPAY_ENTITY_ID_VISA'),
        'entity_id_mada' => env('HYPERPAY_ENTITY_ID_MADA'),
        'entity_id_applepay' => env('HYPERPAY_ENTITY_ID_APPLEPAY'),
        'entity_id' => env('HYPERPAY_ENTITY_ID'),
        'base_url' => env('HYPERPAY_BASE_URL'),
        'currency' => env('HYPERPAY_CURRENCY', 'SAR'),
        'payment_mode' => env('PAYMENT_MODE', 'Production'),
    ],
    'google' => [
        'key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    // apple
    'apple' => [
        'env' => env('APPLE_ENV'),
        'url' => env('APPLE_ENV') == 'production' ? env("APPLE_PRODUCTION_URL") : env("APPLE_SENDBOX_URL"),
        'bundle_id' => env('APPLE_BUNDLE_ID'),
        'shared_secret' => env('APPLE_SHARED_SECRET'),
    ]

];
