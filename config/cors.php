<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Automatically set allowed origins based on APP_ENV
    'allowed_origins' => env('APP_ENV') === 'production' 
        ? [
            env('FRONTEND_URL', 'https://yourdomain.com'), // Production frontend URL
            // Add additional production origins if needed
        ]
        : [
            env('FRONTEND_URL', 'http://localhost:5173'), // Local development
            'http://localhost:3000', // Common React dev port
            'http://127.0.0.1:5173',
            'http://127.0.0.1:3000',
            'http://10.139.48.97:5173', // ✅ Specific local IP for frontend
        ],

    // ✅ Allow local IP addresses in development (192.168.x.x, 10.x.x.x, 172.16-31.x.x)
    // Note: The middleware also handles this dynamically, but patterns provide additional support
    'allowed_origins_patterns' => env('APP_ENV') === 'production' 
        ? [] 
        : [
            '#^http://192\.168\.\d+\.\d+:\d+$#',  // 192.168.x.x:port
            '#^http://10\.\d+\.\d+\.\d+:\d+$#',   // 10.x.x.x:port
            '#^http://172\.(1[6-9]|2[0-9]|3[0-1])\.\d+\.\d+:\d+$#', // 172.16-31.x.x:port
        ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Enable credentials support for httpOnly cookies
    'supports_credentials' => true,

];
