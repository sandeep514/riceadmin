<?php

namespace App\Http\Controllers;

use App\CartonSize;
use App\Http\Requests\CartonSizeRequest;
use Session;

class CartonSizeController extends Controller
{
    public function index()
    {
        $records = CartonSize::orderByDesc('id')->get();

        return view('carton-sizes.index', compact('records'));
    }

    public function create()
    {
        return view('carton-sizes.create');
    }

    public function save(CartonSizeRequest $request)
    {
        CartonSize::create($request->only([
            'size',
            'description',
            'status',
        ]));

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
}
