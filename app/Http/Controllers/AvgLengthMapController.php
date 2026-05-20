<?php

namespace App\Http\Controllers;

use App\AvgLengthMap;
use App\RiceFormMilestone3;
use App\RiceName;
use Illuminate\Http\Request;
use Session;

class AvgLengthMapController extends Controller
{
    public function index()
    {
        $records = AvgLengthMap::with(['riceName', 'form', 'wand.getWandType'])
            ->orderByDesc('id')
            ->get();

        return view('avg-length-map.index', compact('records'));
    }

    public function create()
    {
        return view('avg-length-map.create');
    }

    public function save(Request $request)
    {
        $data = $this->validated($request);

        if ($this->duplicateExists($data)) {
            Session::flash('error', 'Error|A map already exists for this category, quality, form, and grade.');

            return back()->withInput();
        }

        AvgLengthMap::create($data);

        Session::flash('success', 'Success|Avg length map saved successfully!');

        return redirect()->route('avg-length-map');
    }

    public function edit($id)
    {
        $model = AvgLengthMap::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $riceNames = $model->quality_type
            ? RiceName::where('type', $model->quality_type)->pluck('name', 'id')
            : collect();
        $riceForms = RiceFormMilestone3::where('status', 1)->orderBy('order')->pluck('name', 'id');

        return view('avg-length-map.edit', compact('model', 'riceNames', 'riceForms'));
    }

    public function update(Request $request, $id)
    {
        $model = AvgLengthMap::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $data = $this->validated($request);

        if ($this->duplicateExists($data, (int) $id)) {
            Session::flash('error', 'Error|A map already exists for this category, quality, form, and grade.');

            return back()->withInput();
        }

        $model->update($data);

        Session::flash('success', 'Success|Avg length map updated successfully!');

        return redirect()->route('avg-length-map');
    }

    public function delete($id)
    {
        $model = AvgLengthMap::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');
        } else {
            $model->delete();
            Session::flash('success', 'Success|Record deleted successfully!');
        }

        return back();
    }

    private function validated(Request $request): array
    {
        $request->validate([
            'quality_type' => 'required|in:basmati,non-basmati',
            'rice_name_id' => 'required|exists:rice_names,id',
            'form_id' => 'required|exists:rice_form_milestone3,id',
            'wand_id' => 'required|exists:wand,id',
            'avg_length' => 'required|numeric|min:0',
        ]);

        return [
            'quality_type' => $request->quality_type,
            'rice_name_id' => (int) $request->rice_name_id,
            'form_id' => (int) $request->form_id,
            'wand_id' => (int) $request->wand_id,
            'avg_length' => round((float) $request->avg_length, 2),
        ];
    }

    private function duplicateExists(array $data, ?int $exceptId = null): bool
    {
        $q = AvgLengthMap::query()
            ->where('quality_type', $data['quality_type'])
            ->where('rice_name_id', $data['rice_name_id'])
            ->where('form_id', $data['form_id'])
            ->where('wand_id', $data['wand_id']);

        if ($exceptId !== null) {
            $q->where('id', '!=', $exceptId);
        }

        return $q->exists();
    }
}
