<?php

namespace App\Http\Controllers;

use App\Http\Requests\VendorPackingTypeRequest;
use App\VendorPackingType;
use Session;

class VendorPackingTypeController extends Controller
{
    public function index()
    {
        $records = VendorPackingType::orderByDesc('id')->get();

        return view('vendor-packing-types.index', compact('records'));
    }

    public function create()
    {
        return view('vendor-packing-types.create');
    }

    public function save(VendorPackingTypeRequest $request)
    {
        VendorPackingType::create($request->only([
            'name',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Vendor packing type saved successfully!');

        return redirect()->route('vendor-packing-types');
    }

    public function edit($id)
    {
        $model = VendorPackingType::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        return view('vendor-packing-types.edit', compact('model'));
    }

    public function update(VendorPackingTypeRequest $request, $id)
    {
        $model = VendorPackingType::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($request->only([
            'name',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Vendor packing type updated successfully!');

        return redirect()->route('vendor-packing-types');
    }

    public function delete($id)
    {
        $model = VendorPackingType::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|Vendor packing type deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = VendorPackingType::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === VendorPackingType::STATUS_ACTIVE
            ? VendorPackingType::STATUS_INACTIVE
            : VendorPackingType::STATUS_ACTIVE;
        $model->save();

        $msg = (int) $model->status === VendorPackingType::STATUS_ACTIVE
            ? 'Success|Vendor packing type marked as active.'
            : 'Success|Vendor packing type marked as inactive.';

        Session::flash('success', $msg);

        return back();
    }
}
