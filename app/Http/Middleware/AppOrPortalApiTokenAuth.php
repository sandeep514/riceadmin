<?php

namespace App\Http\Middleware;

use App\Support\ClientPlatform;
use App\User;
use Closure;
use Illuminate\Http\Request;

/**
 * Soft auth for routes shared by the legacy native app and the portal RN app.
 *
 * - No token → allow (legacy clients that never sent Bearer)
 * - Token matches legacy app user (userType 1) OR portal user → allow
 * - Stale / wrong token → 401 session_expired
 */
class AppOrPortalApiTokenAuth
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

        $platform = 'mobile';

        if (! $user) {
            $platform = ClientPlatform::fromRequest($request);
            $allowedFrom = config('portal.api_token_user_from', ['web']);
            $allowNullFrom = (bool) config('portal.api_token_allow_null_user_from', true);

            $user = User::findByPortalApiToken($token, function ($query) use ($allowedFrom, $allowNullFrom) {
                $query->where('userType', 2)
                    ->where(function ($q) use ($allowedFrom, $allowNullFrom) {
                        $q->whereIn('user_from', $allowedFrom);
                        if ($allowNullFrom) {
                            $q->orWhereNull('user_from')->orWhere('user_from', '');
                        }
                    });
            }, $platform);
        }

        if (! $user) {
            $candidateId = $request->input('user_id')
                ?? $request->input('id')
                ?? $request->route('userId')
                ?? $request->route('user_id');

            if ($blockedMessage = User::authAccessBlockedMessageForUserId($candidateId)) {
                return response()->json([
                    'status' => false,
                    'message' => $blockedMessage,
                    'session_expired' => true,
                ], 403);
            }

            return response()->json([
                'status' => false,
                'message' => 'Unauthorized: session expired. Please login again.',
                'session_expired' => true,
            ], 401);
        }

        if ($blockedMessage = $user->authAccessBlockedMessage()) {
            return response()->json([
                'status' => false,
                'message' => $blockedMessage,
                'session_expired' => true,
            ], 403);
        }

        if ($request->has('id') && $request->input('id') !== '' && $request->input('id') !== null
            && (int) $request->input('id') !== (int) $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: You are not allowed to perform this action for another user.',
            ], 403);
        }

        foreach (['userId', 'user_id', 'id'] as $param) {
            $routeValue = $request->route($param);
            if ($routeValue !== null && (int) $routeValue !== (int) $user->id) {
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

        $request->attributes->set('auth_platform', $user->getAttribute('auth_platform') ?: $platform);
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
