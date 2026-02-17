<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    public function handle($request, Closure $next)
    {
        $allowedOrigins = config('cors.allowed_origins', []);
        $allowedPatterns = config('cors.allowed_origins_patterns', []);
        $supportsCredentials = config('cors.supports_credentials', false);
        
        $origin = $request->headers->get('Origin');
        $isDevelopment = env('APP_ENV') !== 'production';
        
        // ✅ Handle preflight OPTIONS requests BEFORE processing
        if ($request->getMethod() === 'OPTIONS') {
            $allowedOrigin = $this->getAllowedOrigin($origin, $allowedOrigins, $allowedPatterns, $isDevelopment);
            
            return response()->json([], 200)
                ->header('Access-Control-Allow-Origin', $allowedOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
                ->header('Access-Control-Allow-Credentials', $supportsCredentials ? 'true' : 'false')
                ->header('Access-Control-Max-Age', '86400');
        }

        // Process the request
        $response = $next($request);

        // Add CORS headers to the response
        $allowedOrigin = $this->getAllowedOrigin($origin, $allowedOrigins, $allowedPatterns, $isDevelopment);
        
        $response->headers->set('Access-Control-Allow-Origin', $allowedOrigin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
        $response->headers->set('Access-Control-Expose-Headers', 'Content-Type, Authorization');
        
        if ($supportsCredentials) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }

    /**
     * Determine the allowed origin for the request
     */
    private function getAllowedOrigin($origin, $allowedOrigins, $allowedPatterns, $isDevelopment)
    {
        if (!$origin) {
            return $allowedOrigins[0] ?? '*';
        }

        // Check exact match
        if (in_array($origin, $allowedOrigins)) {
            return $origin;
        }

        // Check pattern match
        if (!empty($allowedPatterns)) {
            foreach ($allowedPatterns as $pattern) {
                if (preg_match($pattern, $origin)) {
                    return $origin;
                }
            }
        }

        // ✅ In development, allow any local IP or localhost
        if ($isDevelopment) {
            $parsedOrigin = parse_url($origin);
            $host = $parsedOrigin['host'] ?? '';
            
            // Allow localhost, 127.0.0.1, or private IP ranges
            if (
                $host === 'localhost' ||
                $host === '127.0.0.1' ||
                preg_match('/^192\.168\./', $host) ||
                preg_match('/^10\./', $host) ||
                preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $host)
            ) {
                return $origin;
            }
        }

        // Fallback to first allowed origin or *
        return $allowedOrigins[0] ?? '*';
    }
}