<?php

namespace App\Http\Controllers;

use App\VendorIcdLocation;

class VendorIcdLocationController extends AbstractVendorNameMasterController
{
    protected function modelClass(): string
    {
        return VendorIcdLocation::class;
    }

    protected function viewFolder(): string
    {
        return 'vendor-icd-locations';
    }

    protected function indexRoute(): string
    {
        return 'vendor-icd-locations';
    }

    protected function label(): string
    {
        return 'ICD location';
    }

    protected function nameColumn(): string
    {
        return 'name';
    }
}
