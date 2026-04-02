<?php

$appUrlPath = parse_url(env('APP_URL', 'http://localhost'), PHP_URL_PATH);
$appUrlPath = is_string($appUrlPath) ? trim($appUrlPath, '/') : '';

$paths = ['api/*', 'sanctum/csrf-cookie'];
if ($appUrlPath !== '') {
    $paths[] = $appUrlPath . '/api/*';
    $paths[] = $appUrlPath . '/sanctum/csrf-cookie';
}

$fromEnv = array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', ''))));

$isProduction = env('APP_ENV') === 'production';

$defaultOrigins = $isProduction
    ? array_filter([
        env('FRONTEND_URL'),
        'https://snjtradelink.com',
        'https://www.snjtradelink.com',
    ])
    : [
        env('FRONTEND_URL', 'http://localhost:5173'),
        'http://localhost:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:3000',
        'http://10.139.48.97:5173',
    ];

$allowedOrigins = array_values(array_unique(array_filter(array_merge($fromEnv, $defaultOrigins))));

$allowedOriginsPatterns = $isProduction
    ? array_values(array_filter([
        env('CORS_ALLOWED_ORIGINS_REGEX')
            ? '#' . trim(env('CORS_ALLOWED_ORIGINS_REGEX'), '#') . '#'
            : null,
        '#^https://([a-zA-Z0-9-]+\.)*snjtradelink\.com$#',
    ]))
    : [
        '#^http://192\.168\.\d+\.\d+:\d+$#',
        '#^http://10\.\d+\.\d+\.\d+:\d+$#',
        '#^http://172\.(1[6-9]|2[0-9]|3[0-1])\.\d+\.\d+:\d+$#',
    ];

return [

    'paths' => $paths,

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => $allowedOriginsPatterns,

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
