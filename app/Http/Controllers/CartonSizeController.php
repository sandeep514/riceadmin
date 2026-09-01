<?php

namespace App\Http\Controllers;

use App\CartonSize;
use App\Http\Requests\CartonSizeRequest;
use App\Support\MasterOrderUpdater;
use Illuminate\Http\Request;
use Session;

class CartonSizeController extends Controller
{
    public function index()
    {
        $records = CartonSize::orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('id')
            ->get();

        return view('carton-sizes.index', compact('records'));
    }

    public function create()
    {
        return view('carton-sizes.create');
    }

    public function save(CartonSizeRequest $request)
    {
        CartonSize::create([
            'size' => $request->input('size'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'order_no' => MasterOrderUpdater::nextOrder(CartonSize::class),
        ]);

        Session::flash('success', 'Success|Carton size saved successfully!');

        return redirect()->route('carton-sizes');
    }

    public function edit($id)
    {
        $model = CartonSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        return view('carton-sizes.edit', compact('model'));
    }

    public function update(CartonSizeRequest $request, $id)
    {
        $model = CartonSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($request->only([
            'size',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Carton size updated successfully!');

        return redirect()->route('carton-sizes');
    }

    public function delete($id)
    {
        $model = CartonSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|Carton size deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = CartonSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === CartonSize::STATUS_ACTIVE
            ? CartonSize::STATUS_INACTIVE
            : CartonSize::STATUS_ACTIVE;
        $model->save();

        $msg = (int) $model->status === CartonSize::STATUS_ACTIVE
            ? 'Success|Carton size marked as active.'
            : 'Success|Carton size marked as inactive.';

        Session::flash('success', $msg);

        return back();
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:carton_sizes,id',
            'order_no' => 'required|integer|min:1',
        ]);

        MasterOrderUpdater::swap(CartonSize::class, (int) $request->id, (int) $request->order_no);
        Session::flash('success', 'Success|Carton size order updated successfully.');

        return back();
    }
}
