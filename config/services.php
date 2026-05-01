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

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        'timeout' => (int) env('GEMINI_TIMEOUT', 8),
        'connect_timeout' => (int) env('GEMINI_CONNECT_TIMEOUT', 3),
    ],

    'product_export' => [
        // VND per 1 USD. Admins can override this from System settings.
        'usd_rate' => (float) env('PRODUCT_EXPORT_USD_RATE', 26295.55),
        'run_inline' => (bool) env('PRODUCT_EXPORT_RUN_INLINE', false),
        'ai_translation' => (bool) env('PRODUCT_EXPORT_AI_TRANSLATION', true),
        'chunk_rows' => (int) env('PRODUCT_EXPORT_CHUNK_ROWS', 20),
        'chunk_seconds' => (int) env('PRODUCT_EXPORT_CHUNK_SECONDS', 45),
        'fallback_chunk_rows' => (int) env('PRODUCT_EXPORT_FALLBACK_CHUNK_ROWS', 3),
        'fallback_chunk_seconds' => (int) env('PRODUCT_EXPORT_FALLBACK_CHUNK_SECONDS', 8),
    ],

];
