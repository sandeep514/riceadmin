<?php

namespace App\Http\Controllers;

use App\Http\Requests\MachineryEquipmentRequest;
use App\MachineryEquipment;
use Session;

class MachineryEquipmentController extends Controller
{
    public function index()
    {
        $records = MachineryEquipment::orderByDesc('id')->get();

        return view('machinery-equipments.index', compact('records'));
    }

    public function create()
    {
        return view('machinery-equipments.create');
    }

    public function save(MachineryEquipmentRequest $request)
    {
        MachineryEquipment::create($request->only([
            'name',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Machinery equipment saved successfully!');

        return redirect()->route('machinery-equipments');
    }

    public function edit($id)
    {
        $model = MachineryEquipment::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        return view('machinery-equipments.edit', compact('model'));
    }

    public function update(MachineryEquipmentRequest $request, $id)
    {
        $model = MachineryEquipment::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($request->only([
            'name',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Machinery equipment updated successfully!');

        return redirect()->route('machinery-equipments');
    }

    public function delete($id)
    {
        $model = MachineryEquipment::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|Machinery equipment deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = MachineryEquipment::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === MachineryEquipment::STATUS_ACTIVE
            ? MachineryEquipment::STATUS_INACTIVE
            : MachineryEquipment::STATUS_ACTIVE;
        $model->save();

        $msg = (int) $model->status === MachineryEquipment::STATUS_ACTIVE
            ? 'Success|Machinery equipment marked as active.'
            : 'Success|Machinery equipment marked as inactive.';

        Session::flash('success', $msg);

        return back();
    }
}
