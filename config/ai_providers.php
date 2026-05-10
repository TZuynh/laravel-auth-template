<?php

return [
    'default' => env('AI_PROVIDER_DEFAULT', 'openai'),

    'fallbacks' => [
        'text' => ['openai'],
        'image' => ['openai'],
        'video' => ['runway', 'kling'],
        'voice' => ['elevenlabs'],
    ],

    'rate_limits' => [
        'openai' => ['max_attempts' => 60, 'decay_seconds' => 60],
        'kling' => ['max_attempts' => 20, 'decay_seconds' => 60],
        'runway' => ['max_attempts' => 20, 'decay_seconds' => 60],
        'elevenlabs' => ['max_attempts' => 40, 'decay_seconds' => 60],
    ],

    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'text_model' => env('OPENAI_TEXT_MODEL', 'gpt-4.1-mini'),
            'image_model' => env('OPENAI_IMAGE_MODEL', 'gpt-image-1'),
        ],
        'kling' => [
            'api_key' => env('KLING_API_KEY'),
            'base_url' => env('KLING_BASE_URL', 'https://api.klingai.com'),
            'video_model' => env('KLING_VIDEO_MODEL', 'kling-v1-6'),
        ],
        'runway' => [
            'api_key' => env('RUNWAY_API_KEY'),
            'base_url' => env('RUNWAY_BASE_URL', 'https://api.dev.runwayml.com'),
            'api_version' => env('RUNWAY_API_VERSION', '2024-11-06'),
            'video_model' => env('RUNWAY_VIDEO_MODEL', 'gen4.5'),
            'image_model' => env('RUNWAY_IMAGE_MODEL', 'gen4_image'),
        ],
        'elevenlabs' => [
            'api_key' => env('ELEVENLABS_API_KEY'),
            'base_url' => env('ELEVENLABS_BASE_URL', 'https://api.elevenlabs.io/v1'),
            'voice_model' => env('ELEVENLABS_MODEL', 'eleven_multilingual_v2'),
        ],
    ],
];
