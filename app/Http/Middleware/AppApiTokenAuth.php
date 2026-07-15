<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Http\Request;

/**
 * Single-device native app session: Bearer / api_token must match users.mobile_api_token.
 * A second login rotates that column, so the first phone gets 401 here.
 */
class AppApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = null;

        $authHeader = $request->header('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            $token = trim($matches[1]);
        }

        if (! $token) {
            $token = $request->header('X-API-TOKEN');
        }

        if (! $token) {
            $token = $request->input('api_token')
                ?? $request->input('token')
                ?? $request->query('api_token')
                ?? $request->query('token');
            $token = $token !== null ? trim((string) $token) : null;
        }

        if (! $token) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized: session expired. Please login again.',
                'session_expired' => true,
            ], 401);
        }

        $user = User::query()
            ->where('userType', 1)
            ->where('mobile_api_token', $token)
            ->first();

        // Legacy: some older builds may still send a token that only lives on api_token.
        if (! $user) {
            $user = User::query()
                ->where('userType', 1)
                ->where('api_token', $token)
                ->where(function ($q) {
                    $q->whereNull('mobile_api_token')->orWhere('mobile_api_token', '');
                })
                ->first();
        }

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

        // updateUserToken* payloads use body "id" as the app user id.
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
}
