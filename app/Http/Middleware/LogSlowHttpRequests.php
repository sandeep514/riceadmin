<?php

namespace App\Http\Middleware;

use App\Services\SqlQueryLogService;
use Closure;
use Illuminate\Http\Request;

/**
 * Records slow web/API requests (with URL + route) into sql_query_logs
 * so the admin dashboard can show which endpoint is slow.
 */
class LogSlowHttpRequests
{
    public function handle(Request $request, Closure $next)
    {
        $started = microtime(true);

        $response = $next($request);

        try {
            $durationMs = (int) round((microtime(true) - $started) * 1000);
            $status = method_exists($response, 'getStatusCode') ? (int) $response->getStatusCode() : null;
            app(SqlQueryLogService::class)->logSlowHttpRequest($request, $durationMs, $status);
        } catch (\Throwable $e) {
            // Never break the response because of logging.
        }

        return $response;
    }
}
