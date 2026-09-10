<?php

namespace App\Http\Controllers;

use App\VendorDestinationPort;

class VendorDestinationPortController extends AbstractVendorNameMasterController
{
    protected function modelClass(): string
    {
        return VendorDestinationPort::class;
    }

    protected function viewFolder(): string
    {
        return 'vendor-destination-ports';
    }

    protected function indexRoute(): string
    {
        return 'vendor-destination-ports';
    }

    protected function label(): string
    {
        return 'Destination port';
    }

    protected function nameColumn(): string
    {
        return 'name';
    }
}
