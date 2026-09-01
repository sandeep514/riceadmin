<?php

namespace App\Http\Controllers;

use App\BagSize;
use App\Http\Requests\BagSizeRequest;
use App\Support\MasterOrderUpdater;
use Illuminate\Http\Request;
use Session;

class BagSizeController extends Controller
{
    public function index()
    {
        $records = BagSize::orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('id')
            ->get();

        return view('bag-sizes.index', compact('records'));
    }

    public function create()
    {
        return view('bag-sizes.create');
    }

    public function save(BagSizeRequest $request)
    {
        BagSize::create([
            'size' => $request->input('size'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'order_no' => MasterOrderUpdater::nextOrder(BagSize::class),
        ]);

        Session::flash('success', 'Success|Bag size saved successfully!');

        return redirect()->route('bag-sizes');
    }

    public function edit($id)
    {
        $model = BagSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        return view('bag-sizes.edit', compact('model'));
    }

    public function update(BagSizeRequest $request, $id)
    {
        $model = BagSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($request->only([
            'size',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Bag size updated successfully!');

        return redirect()->route('bag-sizes');
    }

    public function delete($id)
    {
        $model = BagSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|Bag size deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = BagSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === BagSize::STATUS_ACTIVE
            ? BagSize::STATUS_INACTIVE
            : BagSize::STATUS_ACTIVE;
        $model->save();

        $msg = (int) $model->status === BagSize::STATUS_ACTIVE
            ? 'Success|Bag size marked as active.'
            : 'Success|Bag size marked as inactive.';

        Session::flash('success', $msg);

        return back();
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:bag_sizes,id',
            'order_no' => 'required|integer|min:1',
        ]);

        MasterOrderUpdater::swap(BagSize::class, (int) $request->id, (int) $request->order_no);
        Session::flash('success', 'Success|Bag size order updated successfully.');

        return back();
    }
}
