<?php

namespace App\Http\Middleware;

use App\WebUserSubscriptionModel;
use Closure;
use Illuminate\Http\Request;

class SignedOrPortalApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $signature = (string) $request->query('signature', '');
        if ($signature !== '') {
            $id = (int) $request->route('id');
            if ($id > 0 && WebUserSubscriptionModel::invoiceAccessTokenIsValid($id, $signature)) {
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
