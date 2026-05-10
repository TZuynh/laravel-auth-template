<?php

return [
    'python_worker' => [
        'base_url' => env('AI_WORKER_BASE_URL', 'http://127.0.0.1:8088'),
        'api_key' => env('AI_WORKER_API_KEY'),
        'timeout' => (int) env('AI_WORKER_TIMEOUT', 180),
        'retry_times' => (int) env('AI_WORKER_RETRY_TIMES', 2),
        'retry_sleep' => (int) env('AI_WORKER_RETRY_SLEEP', 750),
    ],

    'storage' => [
        'disk' => env('AI_VIDEO_STORAGE_DISK', 'public'),
        'cdn_url' => env('AI_VIDEO_CDN_URL'),
    ],

    'queues' => [
        'text' => env('AI_VIDEO_TEXT_QUEUE', 'ai-text'),
        'gpu' => env('AI_VIDEO_GPU_QUEUE', 'ai-gpu'),
        'render' => env('AI_VIDEO_RENDER_QUEUE', 'render'),
        'webhooks' => env('AI_VIDEO_WEBHOOK_QUEUE', 'webhooks'),
    ],

    'limits' => [
        'prompt_max_chars' => (int) env('AI_VIDEO_PROMPT_MAX_CHARS', 5000),
        'max_scenes' => (int) env('AI_VIDEO_MAX_SCENES', 12),
        'max_duration_seconds' => (int) env('AI_VIDEO_MAX_DURATION', 90),
    ],
];

