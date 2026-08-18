<?php

namespace App\Http\Controllers;

use App\Http\Requests\VendorSpecificationRequest;
use App\VendorSpecification;
use Session;

class VendorSpecificationController extends Controller
{
    public function index()
    {
        $records = VendorSpecification::orderByDesc('id')->get();

        return view('vendor-specifications.index', compact('records'));
    }

    public function create()
    {
        return view('vendor-specifications.create');
    }

    public function save(VendorSpecificationRequest $request)
    {
        VendorSpecification::create($request->only([
            'specification',
            'description',
            'spec_for',
            'status',
        ]));

        Session::flash('success', 'Success|Specification saved successfully!');

        return redirect()->route('vendor-specifications');
    }

    public function edit($id)
    {
        $model = VendorSpecification::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        return view('vendor-specifications.edit', compact('model'));
    }

    public function update(VendorSpecificationRequest $request, $id)
    {
        $model = VendorSpecification::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($request->only([
            'specification',
            'description',
            'spec_for',
            'status',
        ]));

        Session::flash('success', 'Success|Specification updated successfully!');

        return redirect()->route('vendor-specifications');
    }

    public function delete($id)
    {
        $model = VendorSpecification::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');
        } else {
            $model->delete();
            Session::flash('success', 'Success|Specification deleted successfully!');
        }

        return back();
    }

    public function changeStatus($id)
    {
        $model = VendorSpecification::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === VendorSpecification::STATUS_ACTIVE
            ? VendorSpecification::STATUS_INACTIVE
            : VendorSpecification::STATUS_ACTIVE;
        $model->save();

        $msg = (int) $model->status === VendorSpecification::STATUS_ACTIVE
            ? 'Success|Specification marked as active.'
            : 'Success|Specification marked as inactive.';

        Session::flash('success', $msg);

        return back();
    }
}
