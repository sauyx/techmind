<?php

return [
    'ai' => [
        'driver' => env('AI_DRIVER', 'template'),
        'base_url' => env('AI_BASE_URL', ''),
        'api_key' => env('AI_API_KEY', ''),
        'model' => env('AI_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('AI_TIMEOUT', 6),
    ],
    'demo_mode' => env('TECHMIND_DEMO_MODE', false),
];
