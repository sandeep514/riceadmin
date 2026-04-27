<?php

namespace App\Http\Controllers;

use App\RiceName;
use App\RiceForm;
use App\WebRiceFormMap;
use Illuminate\Http\Request;
use Session;

class WebRiceFormMapController extends Controller
{
    public function index()
    {
        $records = WebRiceFormMap::with('riceName')->get();
        return view('rice-form-map.index', compact('records'));
    }

    public function create()
    {
        $riceNames = RiceName::pluck('name', 'id');
        $riceForms = RiceForm::pluck('form_name', 'id');
        return view('rice-form-map.create', compact('riceNames', 'riceForms'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'rice_name_id' => 'required|exists:rice_names,id',
            'group_name'   => 'required|string|max:255',
            'form_ids'     => 'required|array|min:1',
            'form_ids.*'   => 'exists:rice_forms,id',
        ]);

        WebRiceFormMap::create([
            'rice_name_id' => $request->rice_name_id,
            'group_name'   => $request->group_name,
            'form_ids'     => $request->form_ids,
        ]);

        Session::flash('success', 'Success|Record Saved Successfully!');
        return redirect()->route('rice-form-map');
    }

    public function edit($id)
    {
        $model = WebRiceFormMap::find($id);
        if ($model == null) {
            Session::flash('error', 'Error|No record found!');
            return back();
        }
        $riceNames = RiceName::pluck('name', 'id');
        $riceForms = RiceForm::pluck('form_name', 'id');
        return view('rice-form-map.edit', compact('model', 'riceNames', 'riceForms'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'rice_name_id' => 'required|exists:rice_names,id',
            'group_name'   => 'required|string|max:255',
            'form_ids'     => 'required|array|min:1',
            'form_ids.*'   => 'exists:rice_forms,id',
        ]);

        $model = WebRiceFormMap::find($id);
        if ($model == null) {
            Session::flash('error', 'Error|No record found!');
            return back();
        }

        $model->update([
            'rice_name_id' => $request->rice_name_id,
            'group_name'   => $request->group_name,
            'form_ids'     => $request->form_ids,
        ]);

        Session::flash('success', 'Success|Record Updated Successfully!');
        return redirect()->route('rice-form-map');
    }

    public function delete($id)
    {
        $model = WebRiceFormMap::find($id);
        if ($model == null) {
            Session::flash('error', 'Error|No record found!');
        } else {
            $model->delete();
            Session::flash('success', 'Success|Record deleted successfully!');
        }
        return back();
    }

    public function getFormsByRiceName($riceNameId)
    {
        $riceName = RiceName::find($riceNameId);
        if (!$riceName) {
            return response()->json([]);
        }
        $forms = RiceForm::pluck('form_name', 'id');
        return response()->json($forms);
    }
}
