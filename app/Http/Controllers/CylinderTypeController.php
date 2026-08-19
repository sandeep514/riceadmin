<?php

namespace App\Http\Controllers;

use App\CylinderType;
use App\Http\Requests\CylinderTypeRequest;
use Session;

class CylinderTypeController extends Controller
{
    public function index()
    {
        $records = CylinderType::orderByDesc('id')->get();

        return view('cylinder-types.index', compact('records'));
    }

    public function create()
    {
        return view('cylinder-types.create');
    }

    public function save(CylinderTypeRequest $request)
    {
        CylinderType::create($request->only([
            'type',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Cylinder type saved successfully!');

        return redirect()->route('cylinder-types');
    }

    public function edit($id)
    {
        $model = CylinderType::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        return view('cylinder-types.edit', compact('model'));
    }

    public function update(CylinderTypeRequest $request, $id)
    {
        $model = CylinderType::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($request->only([
            'type',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Cylinder type updated successfully!');

        return redirect()->route('cylinder-types');
    }

    public function delete($id)
    {
        $model = CylinderType::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|Cylinder type deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = CylinderType::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === CylinderType::STATUS_ACTIVE
            ? CylinderType::STATUS_INACTIVE
            : CylinderType::STATUS_ACTIVE;
        $model->save();

        $msg = (int) $model->status === CylinderType::STATUS_ACTIVE
            ? 'Success|Cylinder type marked as active.'
            : 'Success|Cylinder type marked as inactive.';

        Session::flash('success', $msg);

        return back();
    }
}
