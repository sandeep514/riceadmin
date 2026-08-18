<?php

namespace App\Http\Controllers;

use App\Http\Requests\VendorContainerParticularRequest;
use App\VendorContainerParticular;
use Session;

class VendorContainerParticularController extends Controller
{
    public function index()
    {
        $records = VendorContainerParticular::orderByDesc('id')->get();

        return view('vendor-container-particulars.index', compact('records'));
    }

    public function create()
    {
        return view('vendor-container-particulars.create');
    }

    public function save(VendorContainerParticularRequest $request)
    {
        VendorContainerParticular::create($request->only([
            'particular',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Container particular saved successfully!');

        return redirect()->route('vendor-container-particulars');
    }

    public function edit($id)
    {
        $model = VendorContainerParticular::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        return view('vendor-container-particulars.edit', compact('model'));
    }

    public function update(VendorContainerParticularRequest $request, $id)
    {
        $model = VendorContainerParticular::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($request->only([
            'particular',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Container particular updated successfully!');

        return redirect()->route('vendor-container-particulars');
    }

    public function delete($id)
    {
        $model = VendorContainerParticular::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|Container particular deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = VendorContainerParticular::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === VendorContainerParticular::STATUS_ACTIVE
            ? VendorContainerParticular::STATUS_INACTIVE
            : VendorContainerParticular::STATUS_ACTIVE;
        $model->save();

        $msg = (int) $model->status === VendorContainerParticular::STATUS_ACTIVE
            ? 'Success|Container particular marked as active.'
            : 'Success|Container particular marked as inactive.';

        Session::flash('success', $msg);

        return back();
    }
}
