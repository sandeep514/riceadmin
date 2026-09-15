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
}
