<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Http\Request;

class PortalApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = null;

        $authHeader = $request->header('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            $token = trim($matches[1]);
        }

        if (!$token) {
            $token = $request->header('X-API-TOKEN');
        }

        if (!$token) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized: API token is required.'
            ], 401);
        }

        $allowedFrom = config('portal.api_token_user_from', ['web']);
        $allowNullFrom = (bool) config('portal.api_token_allow_null_user_from', true);

        $user = User::where('api_token', $token)
            ->where(function ($query) use ($allowedFrom, $allowNullFrom) {
                $query->whereIn('user_from', $allowedFrom);
                if ($allowNullFrom) {
                    $query->orWhereNull('user_from')->orWhere('user_from', '');
                }
            })
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: Invalid API token.'
            ], 403);
        }

        if ($user->isAdminDeactivated()) {
            return response()->json([
                'status' => false,
                'message' => 'Your account has been deactivated. Please contact the administrator for further assistance or to reactivate your account.',
            ], 403);
        }

        // Enforce ownership for user-scoped routes/payloads.
        // If an endpoint carries user identity, it must match token owner.
        $routeUserId = $request->route('userId');
        if ($routeUserId !== null && (int) $routeUserId !== (int) $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: You are not allowed to access this user data.'
            ], 403);
        }

        if ($request->has('user_id') && (int) $request->input('user_id') !== (int) $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: You are not allowed to perform this action for another user.'
            ], 403);
        }

        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
