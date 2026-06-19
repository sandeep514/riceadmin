<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reject oversized multipart bodies before PHP discards $_POST/$_FILES (api-friendly JSON 413).
 * Web server must also allow the body (nginx: client_max_body_size, Apache: LimitRequestBody).
 */
class ValidateMultipartPostSize
{
    /** ~5 optional files × 15 MB + form fields */
    private const MAX_BYTES = 83886080; // 80 MB

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldSkipBodySizeValidation($request)) {
            return $next($request);
        }

        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);

        if ($contentLength > 0 && $contentLength > self::MAX_BYTES) {
            return $this->payloadTooLargeResponse($request);
        }

        $postMax = $this->parseSize(ini_get('post_max_size'));
        if ($postMax > 0 && $contentLength > $postMax) {
            return $this->payloadTooLargeResponse($request);
        }

        if (
            $contentLength > 0
            && empty($request->all())
            && empty($request->allFiles())
            && $this->wantsJson($request)
        ) {
            return $this->payloadTooLargeResponse($request);
        }

        return $next($request);
    }

    private function wantsJson(Request $request): bool
    {
        return $request->is('api/*')
            || $request->expectsJson()
            || str_contains((string) $request->header('Accept', ''), 'json');
    }

    private function payloadTooLargeResponse(Request $request): Response
    {
        $message = 'Request body is too large. Maximum upload size is 80 MB total (about 15 MB per file). '
            .'If this persists, increase nginx client_max_body_size or PHP post_max_size on the server.';

        if ($this->wantsJson($request)) {
            return response()->json([
                'status' => false,
                'message' => $message,
                'errors' => ['upload' => [$message]],
            ], 413);
        }

        return response($message, 413);
    }

    /**
     * Read-only count endpoints only need small filter params; ignore accidental oversized bodies.
     */
    private function shouldSkipBodySizeValidation(Request $request): bool
    {
        return $request->is('api/get/all/trades/count');
    }

    private function parseSize(string $size): int
    {
        $size = trim($size);
        if ($size === '' || $size === '-1') {
            return 0;
        }

        $unit = strtolower(substr($size, -1));
        $value = (int) substr($size, 0, -1);

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => (int) $size,
        };
    }
}
