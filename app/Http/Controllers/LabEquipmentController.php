<?php

namespace App\Http\Controllers;

use App\Http\Requests\LabEquipmentRequest;
use App\LabEquipment;
use Session;

class LabEquipmentController extends Controller
{
    public function index()
    {
        $records = LabEquipment::orderByDesc('id')->get();

        return view('lab-equipments.index', compact('records'));
    }

    public function create()
    {
        return view('lab-equipments.create');
    }

    public function save(LabEquipmentRequest $request)
    {
        LabEquipment::create($request->only([
            'name',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Lab equipment saved successfully!');

        return redirect()->route('lab-equipments');
    }

    public function edit($id)
    {
        $model = LabEquipment::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        return view('lab-equipments.edit', compact('model'));
    }

    public function update(LabEquipmentRequest $request, $id)
    {
        $model = LabEquipment::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($request->only([
            'name',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Lab equipment updated successfully!');

        return redirect()->route('lab-equipments');
    }

    public function delete($id)
    {
        $model = LabEquipment::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|Lab equipment deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = LabEquipment::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === LabEquipment::STATUS_ACTIVE
            ? LabEquipment::STATUS_INACTIVE
            : LabEquipment::STATUS_ACTIVE;
        $model->save();

        $msg = (int) $model->status === LabEquipment::STATUS_ACTIVE
            ? 'Success|Lab equipment marked as active.'
            : 'Success|Lab equipment marked as inactive.';

        Session::flash('success', $msg);

        return back();
    }
}
