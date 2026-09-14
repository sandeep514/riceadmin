<?php

namespace App\Http\Controllers;

use App\Support\VendorProductCatalog;
use App\Services\VendorProductAdminNotificationService;
use Illuminate\Http\Request;
use Session;

class VendorProductReviewController extends Controller
{
    public const STATUS_AWAITING_VENDOR = 2;

    public function askVendor(Request $request, string $kind, $id)
    {
        $request->validate([
            'message' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $models = VendorProductCatalog::productModels();
        if (! isset($models[$kind])) {
            Session::flash('error', 'Error|Unknown product type.');

            return back();
        }

        $product = $models[$kind]::query()->find((int) $id);
        if ($product === null) {
            Session::flash('error', 'Error|Product not found.');

            return back();
        }

        $message = trim((string) $request->input('message'));
        $attrs = [
            'status' => self::STATUS_AWAITING_VENDOR,
        ];
        if (in_array('admin_message', $product->getFillable(), true)) {
            $attrs['admin_message'] = $message;
        }
        $product->update($attrs);

        VendorProductAdminNotificationService::notifyVendorMessage(
            $kind,
            $product->fresh(),
            $message
        );

        Session::flash('success', 'Success|Vendor has been emailed with your message.');

        return back();
    }
}
