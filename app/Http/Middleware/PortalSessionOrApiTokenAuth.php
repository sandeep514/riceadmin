<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * For portal routes that should work with either:
 * - web session cookie (after OTP login), or
 * - Bearer / X-API-TOKEN (same rules as PortalApiTokenAuth).
 *
 * Avoids requiring the API token when the SPA already has a valid session.
 */
class PortalSessionOrApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $user = null;

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            if ((int) ($user->userType ?? 0) !== 2) {
                return response()->json(['status' => false, 'message' => 'Forbidden.'], 403);
            }
        }

        if (! $user) {
            $token = null;
            $authHeader = $request->header('Authorization');
            if ($authHeader && preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
                $token = trim($matches[1]);
            }
            if (! $token) {
                $token = $request->header('X-API-TOKEN');
            }

            if ($token) {
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
            }
        }

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized: sign in or provide a valid API token.',
            ], 401);
        }

        if ($request->has('user_id') && $request->input('user_id') !== '' && $request->input('user_id') !== null) {
            if ((int) $request->input('user_id') !== (int) $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Forbidden: You are not allowed to perform this action for another user.',
                ], 403);
            }
        } else {
            $request->merge(['user_id' => $user->id]);
        }

        $request->setUserResolver(static fn () => $user);

        return $next($request);
    }
}
