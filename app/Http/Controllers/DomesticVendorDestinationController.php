<?php

namespace App\Http\Controllers;

use App\DomesticVendorDestination;

class DomesticVendorDestinationController extends AbstractVendorNameMasterController
{
    protected function modelClass(): string
    {
        return DomesticVendorDestination::class;
    }

    protected function viewFolder(): string
    {
        return 'domestic-vendor-destinations';
    }

    protected function indexRoute(): string
    {
        return 'domestic-vendor-destinations';
    }

    protected function label(): string
    {
        return 'Destination';
    }

    protected function nameColumn(): string
    {
        return 'name';
    }
}
