<?php

namespace App\Http\Controllers;

use App\VendorForwarderChargeTitle;

class VendorForwarderChargeTitleController extends AbstractVendorNameMasterController
{
    protected function modelClass(): string
    {
        return VendorForwarderChargeTitle::class;
    }

    protected function viewFolder(): string
    {
        return 'vendor-forwarder-charge-titles';
    }

    protected function indexRoute(): string
    {
        return 'vendor-forwarder-charge-titles';
    }

    protected function label(): string
    {
        return 'Forwarder charge title';
    }

    protected function nameColumn(): string
    {
        return 'name';
    }

    /**
     * @return array{name:string, description:?string, status:int, is_required:int}
     */
    protected function validatePayload(\Illuminate\Http\Request $request, ?int $ignoreId = null): array
    {
        $payload = parent::validatePayload($request, $ignoreId);
        $payload['is_required'] = (int) $request->input('is_required', 1) === 1 ? 1 : 0;

        return $payload;
    }
}
