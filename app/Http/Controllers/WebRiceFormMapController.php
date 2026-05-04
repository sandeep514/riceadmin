<?php

namespace App\Http\Controllers;

use App\RiceName;
use App\RiceForm;
use App\WandTypeModel;
use App\WandModel;
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
        $wandTypes = WandTypeModel::orderBy('order', 'ASC')->get();
        return view('rice-form-map.create', compact('wandTypes'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'rice_type'    => 'required|in:basmati,non-basmati',
            'rice_name_id' => 'required|exists:rice_names,id',
            'form_ids'     => 'required|exists:rice_forms,id',
            'wand_ids'     => 'nullable|array',
            'wand_ids.*'   => 'exists:wand,id',
        ]);

        WebRiceFormMap::create([
            'rice_type'    => $request->rice_type,
            'rice_name_id' => $request->rice_name_id,
            'group_name'   => $request->group_name ?? '',
            'form_ids'     => $request->form_ids,
            'wand_ids'     => $request->wand_ids ?? [],
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
        $wandTypes = WandTypeModel::orderBy('order', 'ASC')->get();
        // Pre-load rice names and forms for the selected rice_type
        $riceNames = $model->rice_type
            ? RiceName::where('type', $model->rice_type)->pluck('name', 'id')
            : collect();
        $riceForms = $model->rice_name_id
            ? RiceForm::where('type', $model->rice_type)->pluck('form_name', 'id')
            : collect();
        return view('rice-form-map.edit', compact('model', 'wandTypes', 'riceNames', 'riceForms'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'rice_type'    => 'required|in:basmati,non-basmati',
            'rice_name_id' => 'required|exists:rice_names,id',
            'form_ids'     => 'required|exists:rice_forms,id',
            'wand_ids'     => 'nullable|array',
            'wand_ids.*'   => 'exists:wand,id',
        ]);

        $model = WebRiceFormMap::find($id);
        if ($model == null) {
            Session::flash('error', 'Error|No record found!');
            return back();
        }

        $model->update([
            'rice_type'    => $request->rice_type,
            'rice_name_id' => $request->rice_name_id,
            'group_name'   => $request->group_name ?? '',
            'form_ids'     => $request->form_ids,
            'wand_ids'     => $request->wand_ids ?? [],
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

    // AJAX: get rice names filtered by type (basmati / non-basmati)
    public function getRiceNamesByType($type)
    {
        $names = RiceName::where('type', $type)->pluck('name', 'id');
        return response()->json($names);
    }

    // AJAX: get rice forms filtered by rice type
    public function getFormsByType($type)
    {
        $forms = RiceForm::where('type', $type)->pluck('form_name', 'id');
        return response()->json($forms);
    }

    // AJAX: get wands for a rice name, showing type + value (e.g. "Wand - 8.50+mm")
    public function getWandsByRiceName($riceNameId)
    {
        $wands = WandModel::with('getWandType')
            ->where('RiceNameId', $riceNameId)
            ->where('status', 1)
            ->orderBy('order')
            ->get()
            ->mapWithKeys(function ($wand) {
                $label = $wand->getWandType ? $wand->getWandType->type . ' - ' . $wand->value : $wand->value;
                return [$wand->id => $label];
            });
        return response()->json($wands);
    }
}
