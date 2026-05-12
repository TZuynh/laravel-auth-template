<?php

return [
    'render' => [
        'default_provider' => env('BULK_AI_VIDEO_RENDER_PROVIDER', 'remotion'),
        'queue' => env('BULK_AI_VIDEO_QUEUE', env('BULK_AI_VIDEO_GENERATION_QUEUE', env('DB_QUEUE', 'default'))),
        'poll_delay_seconds' => (int) env('SHOTSTACK_POLL_DELAY_SECONDS', 20),
        'max_poll_attempts' => (int) env('SHOTSTACK_MAX_POLL_ATTEMPTS', 45),
    ],

    'generation' => [
        'queue' => env('BULK_AI_VIDEO_GENERATION_QUEUE', env('DB_QUEUE', 'default')),
        'asset_provider' => env('BULK_AI_VIDEO_ASSET_PROVIDER', 'kling'),
        'cloud_video_bridge' => env('BULK_AI_VIDEO_CLOUD_VIDEO_BRIDGE', 'fal'),
        'cloud_image_bridge' => env('BULK_AI_VIDEO_CLOUD_IMAGE_BRIDGE', 'fal'),
        'generate_scene_video_assets' => (bool) env('BULK_AI_VIDEO_GENERATE_SCENE_VIDEO_ASSETS', false),
        'max_versions' => 1,
    ],
];
