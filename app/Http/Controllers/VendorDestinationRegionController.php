<?php

namespace App\Http\Controllers;

use App\VendorDestinationRegion;

class VendorDestinationRegionController extends AbstractVendorNameMasterController
{
    protected function modelClass(): string
    {
        return VendorDestinationRegion::class;
    }

    protected function viewFolder(): string
    {
        return 'vendor-destination-regions';
    }

    protected function indexRoute(): string
    {
        return 'vendor-destination-regions';
    }

    protected function label(): string
    {
        return 'Destination region';
    }

    protected function nameColumn(): string
    {
        return 'name';
    }
}
