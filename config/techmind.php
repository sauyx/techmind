<?php

return [
    'ai' => [
        'driver' => env('AI_DRIVER', 'template'),
        'base_url' => env('AI_BASE_URL', ''),
        'api_key' => env('AI_API_KEY', ''),
        'model' => env('AI_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('AI_TIMEOUT', 6),
        'json_mode' => (bool) env('AI_JSON_MODE', false),
    ],
    'demo_mode' => env('TECHMIND_DEMO_MODE', false),

    'rate_limits' => [
        'tes_submit_per_session' => (int) env('RATE_TES_SESSION', 6),
        'tes_submit_per_ip' => (int) env('RATE_TES_IP', 120),
        'hasil_pdf_per_token' => (int) env('RATE_PDF_TOKEN', 5),
        'hasil_pdf_per_ip' => (int) env('RATE_PDF_IP', 60),
        'feedback_per_token' => (int) env('RATE_FEEDBACK_TOKEN', 5),
        'feedback_per_ip' => (int) env('RATE_FEEDBACK_IP', 60),
    ],
];
