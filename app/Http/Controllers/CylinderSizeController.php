<?php

namespace App\Http\Controllers;

use App\CylinderSize;
use App\Http\Requests\CylinderSizeRequest;
use App\Support\MasterOrderUpdater;
use Illuminate\Http\Request;
use Session;

class CylinderSizeController extends Controller
{
    public function index()
    {
        $records = CylinderSize::orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('id')
            ->get();

        return view('cylinder-sizes.index', compact('records'));
    }

    public function create()
    {
        return view('cylinder-sizes.create');
    }

    public function save(CylinderSizeRequest $request)
    {
        CylinderSize::create([
            'size' => $request->input('size'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'order_no' => MasterOrderUpdater::nextOrder(CylinderSize::class),
        ]);

        Session::flash('success', 'Success|Cylinder size saved successfully!');

        return redirect()->route('cylinder-sizes');
    }

    public function edit($id)
    {
        $model = CylinderSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        return view('cylinder-sizes.edit', compact('model'));
    }

    public function update(CylinderSizeRequest $request, $id)
    {
        $model = CylinderSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($request->only([
            'size',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Cylinder size updated successfully!');

        return redirect()->route('cylinder-sizes');
    }

    public function delete($id)
    {
        $model = CylinderSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|Cylinder size deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = CylinderSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === CylinderSize::STATUS_ACTIVE
            ? CylinderSize::STATUS_INACTIVE
            : CylinderSize::STATUS_ACTIVE;
        $model->save();

        $msg = (int) $model->status === CylinderSize::STATUS_ACTIVE
            ? 'Success|Cylinder size marked as active.'
            : 'Success|Cylinder size marked as inactive.';

        Session::flash('success', $msg);

        return back();
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:cylinder_sizes,id',
            'order_no' => 'required|integer|min:1',
        ]);

        MasterOrderUpdater::swap(CylinderSize::class, (int) $request->id, (int) $request->order_no);
        Session::flash('success', 'Success|Cylinder size order updated successfully.');

        return back();
    }
}
