<?php

namespace App\Http\Controllers;

use App\DomesticVendorCountry;
use Session;

class DomesticVendorCountryController extends AbstractVendorNameMasterController
{
    protected function modelClass(): string
    {
        return DomesticVendorCountry::class;
    }

    protected function viewFolder(): string
    {
        return 'domestic-vendor-countries';
    }

    protected function indexRoute(): string
    {
        return 'domestic-vendor-countries';
    }

    protected function label(): string
    {
        return 'Country';
    }

    protected function nameColumn(): string
    {
        return 'name';
    }

    public function delete($id)
    {
        $model = DomesticVendorCountry::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        if ($model->states()->exists()) {
            Session::flash('error', 'Error|Remove states under this country first.');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|Country deleted successfully!');

        return back();
    }
}
