<?php

namespace App\Http\Controllers;

use App\FutureSellQueriesINR;
use App\QualityGradeMap;
use App\RiceFormMilestone3;
use App\RiceName;
use App\SellQueriesINR;
use App\TradeQueriesINR;
use App\WandModel;
use Illuminate\Http\Request;
use Session;

class QualityGradeMapController extends Controller
{
    public function index()
    {
        $records = QualityGradeMap::with(['riceName', 'form'])
            ->orderByDesc('id')
            ->get();

        return view('quality-grade-map.index', compact('records'));
    }

    public function create()
    {
        return view('quality-grade-map.create');
    }

    public function save(Request $request)
    {
        $data = $this->validated($request);

        if ($this->duplicateExists($data)) {
            Session::flash('error', 'Error|A map already exists for this category, quality, and form.');

            return back()->withInput();
        }

        QualityGradeMap::create($data);

        Session::flash('success', 'Success|Quality grade map saved successfully!');

        return redirect()->route('quality-grade-map');
    }

    public function edit($id)
    {
        $model = QualityGradeMap::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $riceNames = $model->quality_type
            ? RiceName::where('type', $model->quality_type)->pluck('name', 'id')
            : collect();
        $riceForms = RiceFormMilestone3::where('status', 1)->orderBy('order')->pluck('name', 'id');

        return view('quality-grade-map.edit', compact('model', 'riceNames', 'riceForms'));
    }

    public function update(Request $request, $id)
    {
        $model = QualityGradeMap::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $data = $this->validated($request);

        if ($this->duplicateExists($data, (int) $id)) {
            Session::flash('error', 'Error|A map already exists for this category, quality, and form.');

            return back()->withInput();
        }

        $model->update($data);

        Session::flash('success', 'Success|Quality grade map updated successfully!');

        return redirect()->route('quality-grade-map');
    }

    public function delete($id)
    {
        $model = QualityGradeMap::find($id);
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
            'wand_ids' => 'required|array|min:1',
            'wand_ids.*' => 'exists:wand,id',
        ]);

        $wandIds = array_values(array_unique(array_map('intval', $request->wand_ids ?? [])));

        return [
            'quality_type' => $request->quality_type,
            'rice_name_id' => (int) $request->rice_name_id,
            'form_id' => (int) $request->form_id,
            'wand_ids' => $wandIds,
            'status' => 1,
        ];
    }

    private function duplicateExists(array $data, ?int $exceptId = null): bool
    {
        $q = QualityGradeMap::query()
            ->where('quality_type', $data['quality_type'])
            ->where('rice_name_id', $data['rice_name_id'])
            ->where('form_id', $data['form_id']);

        if ($exceptId !== null) {
            $q->where('id', '!=', $exceptId);
        }

        return $q->exists();
    }

    /**
     * Distinct wand grades already used on trades / sell queries
     * for the selected quality (rice name) + form.
     */
    public function usedWands(Request $request)
    {
        $request->validate([
            'rice_name_id' => 'required|integer|exists:rice_names,id',
            'form_id' => 'required|integer|exists:rice_form_milestone3,id',
        ]);

        $riceNameId = (int) $request->rice_name_id;
        $formId = (int) $request->form_id;

        $wandIds = collect()
            ->merge(
                TradeQueriesINR::query()
                    ->where('quality', $riceNameId)
                    ->where('qualityForm', $formId)
                    ->whereNotNull('grade')
                    ->where('grade', '>', 0)
                    ->distinct()
                    ->pluck('grade')
            )
            ->merge(
                SellQueriesINR::query()
                    ->where('quality', $riceNameId)
                    ->where('qualityForm', $formId)
                    ->whereNotNull('grade')
                    ->where('grade', '>', 0)
                    ->distinct()
                    ->pluck('grade')
            )
            ->merge(
                FutureSellQueriesINR::query()
                    ->where('quality', $riceNameId)
                    ->where('quality_form', $formId)
                    ->whereNotNull('grade')
                    ->where('grade', '>', 0)
                    ->distinct()
                    ->pluck('grade')
            )
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter()
            ->unique()
            ->values();

        if ($wandIds->isEmpty()) {
            return response()->json([
                'count' => 0,
                'wands' => [],
            ]);
        }

        $wands = WandModel::with('getWandType')
            ->whereIn('id', $wandIds->all())
            ->orderBy('order')
            ->get()
            ->map(function ($wand) {
                $label = $wand->getWandType
                    ? $wand->getWandType->type.' - '.$wand->value
                    : $wand->value;

                return [
                    'id' => $wand->id,
                    'label' => $label,
                ];
            })
            ->values();

        return response()->json([
            'count' => $wands->count(),
            'wands' => $wands,
        ]);
    }
}
