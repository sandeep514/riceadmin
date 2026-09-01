<?php

namespace App\Http\Controllers;

use App\Http\Requests\VendorPackingTypeRequest;
use App\PackingType;
use Session;

class VendorPackingTypeController extends Controller
{
    public function index()
    {
        $records = PackingType::orderByDesc('id')->get();

        return view('vendor-packing-types.index', compact('records'));
    }

    public function create()
    {
        return view('vendor-packing-types.create');
    }

    public function save(VendorPackingTypeRequest $request)
    {
        PackingType::create($request->only([
            'name',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Packing type saved successfully!');

        return redirect()->route('vendor-packing-types');
    }

    public function edit($id)
    {
        $model = PackingType::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        return view('vendor-packing-types.edit', compact('model'));
    }

    public function update(VendorPackingTypeRequest $request, $id)
    {
        $model = PackingType::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($request->only([
            'name',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Packing type updated successfully!');

        return redirect()->route('vendor-packing-types');
    }

    public function delete($id)
    {
        $model = PackingType::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|Packing type deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = PackingType::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === PackingType::STATUS_ACTIVE
            ? PackingType::STATUS_INACTIVE
            : PackingType::STATUS_ACTIVE;
        $model->save();

        $msg = (int) $model->status === PackingType::STATUS_ACTIVE
            ? 'Success|Packing type marked as active.'
            : 'Success|Packing type marked as inactive.';

        Session::flash('success', $msg);

        return back();
    }
}
