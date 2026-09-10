<?php

namespace App\Http\Controllers;

use App\VendorPortType;

class VendorPortTypeController extends AbstractVendorNameMasterController
{
    protected function modelClass(): string
    {
        return VendorPortType::class;
    }

    protected function viewFolder(): string
    {
        return 'vendor-port-types';
    }

    protected function indexRoute(): string
    {
        return 'vendor-port-types';
    }

    protected function label(): string
    {
        return 'Port type';
    }

    protected function nameColumn(): string
    {
        return 'name';
    }
}
