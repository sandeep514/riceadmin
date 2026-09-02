<?php

namespace App\Http\Controllers;

use App\Http\Requests\VendorPackingTypeRequest;
use App\Support\MasterOrderUpdater;
use App\VendorPackingType;
use Illuminate\Http\Request;
use Session;

class VendorPackingTypeController extends Controller
{
    public function index()
    {
        $records = VendorPackingType::orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('id')
            ->get();

        return view('vendor-packing-types.index', compact('records'));
    }

    public function create()
    {
        return view('vendor-packing-types.create');
    }

    public function save(VendorPackingTypeRequest $request)
    {
        VendorPackingType::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'order_no' => MasterOrderUpdater::nextOrder(VendorPackingType::class),
        ]);

        Session::flash('success', 'Success|Bag type saved successfully!');

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

        Session::flash('success', 'Success|Bag type updated successfully!');

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
        Session::flash('success', 'Success|Bag type deleted successfully!');

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
            ? 'Success|Bag type marked as active.'
            : 'Success|Bag type marked as inactive.';

        Session::flash('success', $msg);

        return back();
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:vendor_packing_types,id',
            'order_no' => 'required|integer|min:1',
        ]);

        MasterOrderUpdater::swap(VendorPackingType::class, (int) $request->id, (int) $request->order_no);
        Session::flash('success', 'Success|Bag type order updated successfully.');

        return back();
    }
}
