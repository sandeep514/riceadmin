<?php

/**
 * CORS paths must match Laravel's request path(). The pattern "api/*" only matches
 * when the app is at the web root. Subfolder installs need "*api/*" (matches any path
 * segment before /api/).
 */
$appUrlPath = parse_url(env('APP_URL', 'http://localhost'), PHP_URL_PATH);
$appUrlPath = is_string($appUrlPath) ? trim($appUrlPath, '/') : '';

$paths = [
    'api/*',
    '*api/*',
    'sanctum/csrf-cookie',
    '*sanctum/csrf-cookie',
];
if ($appUrlPath !== '') {
    $paths[] = $appUrlPath . '/api/*';
    $paths[] = $appUrlPath . '/sanctum/csrf-cookie';
}

$fromEnv = array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', ''))));

// Only "local" uses localhost-only defaults. Staging / production / testing need real domains.
$isLocal = env('APP_ENV') === 'local';

// Production SPA: https://sntc.netlify.app/ (Origin header has no trailing slash)
$netlifyFrontendOrigins = [
    'https://sntc.netlify.app',
];

$defaultOrigins = $isLocal
    ? array_values(array_unique(array_filter(array_merge(
        [
            env('FRONTEND_URL', 'http://localhost:5173'),
            'http://localhost:3000',
            'http://127.0.0.1:5173',
            'http://127.0.0.1:3000',
            'http://10.139.48.97:5173',
        ],
        $netlifyFrontendOrigins
    ))))
    : array_values(array_unique(array_filter(array_merge(
        [
            env('FRONTEND_URL'),
            'https://snjtradelink.com',
            'https://www.snjtradelink.com',
            'http://snjtradelink.com',
            'http://www.snjtradelink.com',
        ],
        $netlifyFrontendOrigins
    ))));

$allowedOrigins = array_values(array_unique(array_filter(array_merge($fromEnv, $defaultOrigins))));

$snjtradelinkPatterns = [
    '#^https://([a-zA-Z0-9-]+\.)*snjtradelink\.com(:\d+)?$#',
    '#^http://([a-zA-Z0-9-]+\.)*snjtradelink\.com(:\d+)?$#',
];

// Netlify branch / deploy preview URLs, e.g. https://deploy-preview-12--sntc.netlify.app
$netlifyPatterns = [
    '#^https://[a-z0-9][a-z0-9-]*--sntc\.netlify\.app$#i',
];

$lanPatterns = [
    '#^http://192\.168\.\d+\.\d+:\d+$#',
    '#^http://10\.\d+\.\d+\.\d+:\d+$#',
    '#^http://172\.(1[6-9]|2[0-9]|3[0-1])\.\d+\.\d+:\d+$#',
];

$customRegex = env('CORS_ALLOWED_ORIGINS_REGEX');
$customPattern = $customRegex ? '#' . trim($customRegex, '#') . '#' : null;

$allowedOriginsPatterns = array_values(array_filter(array_merge(
    $customPattern ? [$customPattern] : [],
    $netlifyPatterns,
    $isLocal ? [] : $snjtradelinkPatterns,
    $lanPatterns,
    $isLocal ? [
        '#^http://localhost:\d+$#',
        '#^http://127\.0\.0\.1:\d+$#',
    ] : [],
)));

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
