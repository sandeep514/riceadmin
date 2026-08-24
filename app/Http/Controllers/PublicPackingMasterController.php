<?php

namespace App\Http\Controllers;

use App\PublicPacking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Session;
use Excel;

class PublicPackingMasterController extends Controller
{
    public function index()
    {
        $data = PublicPacking::orderByRaw('`order` IS NULL, `order` ASC')
            ->orderBy('id')
            ->get();

        return View('PublicPacking.create', compact('data'));
    }

    public function create()
    {
        return View('PublicPacking.form', [
            'data' => null,
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'size' => 'required|string|max:256',
            'packing' => 'required|string|max:256',
            'order' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $order = $request->filled('order')
            ? (int) $request->order
            : (((int) PublicPacking::max('order')) + 1);

        PublicPacking::create([
            'size' => $request->size,
            'packing' => $request->packing,
            'order' => $order > 0 ? $order : 1,
            'status' => 1,
        ]);

        Session::flash('success', 'Success|Public packing added successfully.');

        return redirect()->route('public.packing.master');
    }

    public function edit($id)
    {
        $data = PublicPacking::findOrFail($id);

        return View('PublicPacking.form', [
            'data' => $data,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:public_packing_milestone3,id',
            'size' => 'required|string|max:256',
            'packing' => 'required|string|max:256',
            'order' => 'nullable|integer|min:1',
            'status' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $packing = PublicPacking::findOrFail($request->id);
        $packing->update([
            'size' => $request->size,
            'packing' => $request->packing,
            'order' => $request->filled('order') ? (int) $request->order : $packing->order,
            'status' => $request->has('status') ? (int) $request->status : $packing->status,
        ]);

        Session::flash('success', 'Success|Public packing updated successfully.');

        return redirect()->route('public.packing.master');
    }

    public function changeStatus($id)
    {
        $packing = PublicPacking::findOrFail($id);
        $packing->update([
            'status' => (int) $packing->status === 1 ? 0 : 1,
        ]);

        Session::flash('success', 'Success|Status updated successfully.');

        return back();
    }

    public function save(Request $request)
    {
        $excel = Excel::toArray([], $request->file('file'));
        $processedData = [];
        if (count($excel) > 0) {
            if (count($excel[0]) > 2) {
                foreach ($excel[0] as $k => $v) {
                    if (count(array_filter($v)) > 0) {
                        if (count(array_filter($v)) > 1) {
                            if (! in_array('size', array_filter($v))) {
                                if (count(array_filter($v)) == 3) {
                                    if ($k > 1) {
                                        $processedData[] = [
                                            'size' => $v[0],
                                            'packing' => $v[1],
                                            'order' => $v[2],
                                            'status' => 1,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (count($processedData) > 0) {
            PublicPacking::insert($processedData);
            Session::flash('success', 'Success|Public packing imported successfully.');
        } else {
            Session::flash('error', 'Error|No valid rows found in Excel.');
        }

        return back();
    }
}
