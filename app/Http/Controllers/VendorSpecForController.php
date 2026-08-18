<?php

namespace App\Http\Controllers;

use App\Http\Requests\VendorSpecForRequest;
use App\VendorSpecFor;
use App\VendorSpecification;
use Session;

class VendorSpecForController extends Controller
{
    public function index()
    {
        $records = VendorSpecFor::orderByDesc('id')->get();

        return view('vendor-spec-fors.index', compact('records'));
    }

    public function create()
    {
        return view('vendor-spec-fors.create');
    }

    public function save(VendorSpecForRequest $request)
    {
        VendorSpecFor::create($request->only([
            'name',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Spec For saved successfully!');

        return redirect()->route('vendor-spec-fors');
    }

    public function edit($id)
    {
        $model = VendorSpecFor::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        return view('vendor-spec-fors.edit', compact('model'));
    }

    public function update(VendorSpecForRequest $request, $id)
    {
        $model = VendorSpecFor::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($request->only([
            'name',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Spec For updated successfully!');

        return redirect()->route('vendor-spec-fors');
    }

    public function delete($id)
    {
        $model = VendorSpecFor::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        if (VendorSpecification::where('spec_for_id', $id)->exists()) {
            Session::flash('error', 'Error|Cannot delete. This Spec For is used in specifications.');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|Spec For deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = VendorSpecFor::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === VendorSpecFor::STATUS_ACTIVE
            ? VendorSpecFor::STATUS_INACTIVE
            : VendorSpecFor::STATUS_ACTIVE;
        $model->save();

        $msg = (int) $model->status === VendorSpecFor::STATUS_ACTIVE
            ? 'Success|Spec For marked as active.'
            : 'Success|Spec For marked as inactive.';

        Session::flash('success', $msg);

        return back();
    }
}
