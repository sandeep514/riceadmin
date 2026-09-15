<?php

namespace App\Http\Controllers;

use App\VendorCurrency;
use Illuminate\Http\Request;

class VendorCurrencyController extends AbstractVendorNameMasterController
{
    protected function modelClass(): string
    {
        return VendorCurrency::class;
    }

    protected function viewFolder(): string
    {
        return 'vendor-currencies';
    }

    protected function indexRoute(): string
    {
        return 'vendor-currencies';
    }

    protected function label(): string
    {
        return 'Currency';
    }

    protected function nameColumn(): string
    {
        return 'name';
    }

    protected function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $request->merge([
            'name' => strtoupper(trim((string) $request->input('name'))),
        ]);

        return parent::validatePayload($request, $ignoreId);
    }
}
