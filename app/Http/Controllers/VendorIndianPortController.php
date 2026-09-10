<?php

namespace App\Http\Controllers;

use App\VendorIndianPort;

class VendorIndianPortController extends AbstractVendorNameMasterController
{
    protected function modelClass(): string
    {
        return VendorIndianPort::class;
    }

    protected function viewFolder(): string
    {
        return 'vendor-indian-ports';
    }

    protected function indexRoute(): string
    {
        return 'vendor-indian-ports';
    }

    protected function label(): string
    {
        return 'Indian port';
    }

    protected function nameColumn(): string
    {
        return 'name';
    }
}
