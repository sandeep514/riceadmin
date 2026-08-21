<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SignedOrPortalApiToken
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('signature')) {
            if ($request->hasValidSignature() || $request->hasValidSignature(false)) {
                $request->attributes->set('invoice_signed', true);

                return $next($request);
            }

            return response()->json([
                'status' => false,
                'message' => 'Invalid invoice link.',
            ], 403);
        }

        return app(PortalApiTokenAuth::class)->handle($request, $next);
    }
}
