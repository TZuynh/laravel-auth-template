<?php

return [
    'api_key' => env('SHOTSTACK_API_KEY'),
    'base_url' => env('SHOTSTACK_BASE_URL', 'https://api.shotstack.io/edit'),
    'version' => env('SHOTSTACK_VERSION', 'stage'),
    'timeout' => (int) env('SHOTSTACK_TIMEOUT', 120),
    'retry_times' => (int) env('SHOTSTACK_RETRY_TIMES', 3),
    'retry_sleep' => (int) env('SHOTSTACK_RETRY_SLEEP', 1000),
    'queue' => env('SHOTSTACK_QUEUE', 'render'),
];
