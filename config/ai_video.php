<?php

return [
    'ffmpeg' => [
        'binary' => env('FFMPEG_BINARY', 'ffmpeg'),
        'ffprobe_binary' => env('FFPROBE_BINARY', 'ffprobe'),
        'timeout' => (int) env('AI_VIDEO_FFMPEG_TIMEOUT', 1800),
        'fps' => (int) env('AI_VIDEO_FPS', 30),
        'preset' => env('AI_VIDEO_FFMPEG_PRESET', 'veryfast'),
        'crf' => (int) env('AI_VIDEO_FFMPEG_CRF', 20),
    ],

    'remotion' => [
        'entry' => env('AI_VIDEO_REMOTION_ENTRY', base_path('remotion/index.jsx')),
        'composition' => env('AI_VIDEO_REMOTION_COMPOSITION', 'CinematicVideo'),
        'timeout' => (int) env('AI_VIDEO_REMOTION_TIMEOUT', 1800),
        'frame_timeout' => (int) env('AI_VIDEO_REMOTION_FRAME_TIMEOUT', 60000),
        'concurrency' => env('AI_VIDEO_REMOTION_CONCURRENCY', '50%'),
        'npx_binary' => env('NPX_BINARY', PHP_OS_FAMILY === 'Windows' ? 'npx.cmd' : 'npx'),
        'browser_executable' => env('AI_VIDEO_REMOTION_BROWSER_EXECUTABLE'),
        'allow_npx_download' => (bool) env('AI_VIDEO_REMOTION_ALLOW_NPX_DOWNLOAD', false),
    ],

    'workspace' => [
        'root' => storage_path('app/ai-video'),
        'exports' => storage_path('app/public/ai-video/exports'),
    ],

    'audio' => [
        'reference_path' => env('AI_VIDEO_REFERENCE_AUDIO'),
        'use_reference' => (bool) env('AI_VIDEO_USE_REFERENCE_AUDIO', false),
    ],

    'queue' => [
        'name' => env('AI_VIDEO_RENDER_QUEUE', env('DB_QUEUE', 'default')),
        'mode' => env('AI_VIDEO_RENDER_MODE', env('APP_ENV') === 'local' ? 'sync' : 'queue'),
    ],

    'formats' => [
        '9:16' => ['width' => 1080, 'height' => 1920],
        '16:9' => ['width' => 1920, 'height' => 1080],
        '1:1' => ['width' => 1080, 'height' => 1080],
    ],
];
