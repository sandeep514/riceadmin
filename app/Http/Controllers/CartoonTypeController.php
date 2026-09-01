<?php

namespace App\Http\Controllers;

use App\CartoonType;
use App\Http\Requests\CartoonTypeRequest;
use App\Support\MasterOrderUpdater;
use Illuminate\Http\Request;
use Session;

class CartoonTypeController extends Controller
{
    public function index()
    {
        $records = CartoonType::orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('id')
            ->get();

        return view('cartoon-types.index', compact('records'));
    }

    public function create()
    {
        return view('cartoon-types.create');
    }

    public function save(CartoonTypeRequest $request)
    {
        CartoonType::create([
            'type' => $request->input('type'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'order_no' => MasterOrderUpdater::nextOrder(CartoonType::class),
        ]);

        Session::flash('success', 'Success|Cartoon type saved successfully!');

        return redirect()->route('cartoon-types');
    }

    public function edit($id)
    {
        $model = CartoonType::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        return view('cartoon-types.edit', compact('model'));
    }

    public function update(CartoonTypeRequest $request, $id)
    {
        $model = CartoonType::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($request->only([
            'type',
            'description',
            'status',
        ]));

        Session::flash('success', 'Success|Cartoon type updated successfully!');

        return redirect()->route('cartoon-types');
    }

    public function delete($id)
    {
        $model = CartoonType::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|Cartoon type deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = CartoonType::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === CartoonType::STATUS_ACTIVE
            ? CartoonType::STATUS_INACTIVE
            : CartoonType::STATUS_ACTIVE;
        $model->save();

        $msg = (int) $model->status === CartoonType::STATUS_ACTIVE
            ? 'Success|Cartoon type marked as active.'
            : 'Success|Cartoon type marked as inactive.';

        Session::flash('success', $msg);

        return back();
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:cartoon_types,id',
            'order_no' => 'required|integer|min:1',
        ]);

        MasterOrderUpdater::swap(CartoonType::class, (int) $request->id, (int) $request->order_no);
        Session::flash('success', 'Success|Cartoon type order updated successfully.');

        return back();
    }
}
