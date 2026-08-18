<?php

namespace App\Http\Controllers;

use App\Http\Requests\VendorPortChargeRequest;
use App\VendorPortCharge;
use Session;

class VendorPortChargeController extends Controller
{
    public function index()
    {
        $records = VendorPortCharge::orderByDesc('id')->get();

        return view('vendor-port-charges.index', compact('records'));
    }

    public function create()
    {
        return view('vendor-port-charges.create');
    }

    public function save(VendorPortChargeRequest $request)
    {
        VendorPortCharge::create($request->only([
            'charge',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Port charge saved successfully!');

        return redirect()->route('vendor-port-charges');
    }

    public function edit($id)
    {
        $model = VendorPortCharge::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        return view('vendor-port-charges.edit', compact('model'));
    }

    public function update(VendorPortChargeRequest $request, $id)
    {
        $model = VendorPortCharge::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($request->only([
            'charge',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Port charge updated successfully!');

        return redirect()->route('vendor-port-charges');
    }

    public function delete($id)
    {
        $model = VendorPortCharge::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|Port charge deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = VendorPortCharge::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === VendorPortCharge::STATUS_ACTIVE
            ? VendorPortCharge::STATUS_INACTIVE
            : VendorPortCharge::STATUS_ACTIVE;
        $model->save();

        $msg = (int) $model->status === VendorPortCharge::STATUS_ACTIVE
            ? 'Success|Port charge marked as active.'
            : 'Success|Port charge marked as inactive.';

        Session::flash('success', $msg);

        return back();
    }
}
