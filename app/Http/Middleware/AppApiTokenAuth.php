<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Http\Request;

/**
 * Soft single-device native app session middleware.
 *
 * - No token → allow (legacy app that never sent tokens).
 * - Stale/wrong token → 401 session_expired (kicks the previous phone).
 * - Valid token → attach user and enforce ownership when user ids are present.
 */
class AppApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $this->extractToken($request);

        if ($token === null || $token === '') {
            return $next($request);
        }

        $user = User::query()
            ->where('userType', 1)
            ->where(function ($q) use ($token) {
                $q->where('mobile_api_token', $token)
                    ->orWhere('api_token', $token);
            })
            ->first();

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized: session expired. Please login again.',
                'session_expired' => true,
            ], 401);
        }

        if ($user->isAdminDeactivated()) {
            return response()->json([
                'status' => false,
                'message' => 'Your account has been deactivated. Please contact the administrator enquiry@sntcgroup.com for further assistance or to reactivate your account.',
                'session_expired' => true,
            ], 403);
        }

        foreach (['userId', 'user_id', 'id'] as $param) {
            $routeValue = $request->route($param);
            if ($routeValue !== null && (int) $routeValue !== (int) $user->id) {
                // Only treat {id} as user id on known user-scoped routes.
                if ($param === 'id' && ! $this->routeIdIsUserId($request)) {
                    continue;
                }

                return response()->json([
                    'status' => false,
                    'message' => 'Forbidden: You are not allowed to access this user data.',
                ], 403);
            }
        }

        foreach (['user_id', 'userId'] as $inputKey) {
            if ($request->has($inputKey) && $request->input($inputKey) !== '' && $request->input($inputKey) !== null
                && (int) $request->input($inputKey) !== (int) $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Forbidden: You are not allowed to perform this action for another user.',
                ], 403);
            }
        }

        if ($request->is('*/update/user/token') || $request->is('api/update/user/token')) {
            if ($request->has('id') && (int) $request->input('id') !== (int) $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Forbidden: You are not allowed to perform this action for another user.',
                ], 403);
            }
        }

        $request->attributes->set('auth_platform', 'mobile');
        $request->setUserResolver(static fn () => $user);

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            return trim($matches[1]);
        }

        $headerToken = $request->header('X-API-TOKEN');
        if ($headerToken) {
            return trim((string) $headerToken);
        }

        $token = $request->input('api_token')
            ?? $request->input('token')
            ?? $request->query('api_token')
            ?? $request->query('token');

        return $token !== null ? trim((string) $token) : null;
    }

    private function routeIdIsUserId(Request $request): bool
    {
        $path = $request->path();

        return str_contains($path, 'check/user/expired')
            || str_contains($path, 'get/my/bids')
            || str_contains($path, 'get/hot/deals')
            || str_contains($path, 'get/buyer/details');
    }
}
