<?php

namespace App\Http\Controllers;

use App\RiceFormMilestone3;
use App\User;
use App\WebRiceFormMap;
use App\WandModel;
use Illuminate\Http\Request;

class UserInterestsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'rice_type'     => 'nullable|in:basmati,non-basmati',
            'rice_name_id'  => 'nullable|integer|exists:rice_names,id',
            'form_id'       => 'nullable|integer|exists:rice_form_milestone3,id',
            'grade'         => 'nullable|integer|exists:wand,id',
        ]);

        $riceType    = $request->input('rice_type');
        $riceNameId  = $request->filled('rice_name_id') ? (int) $request->rice_name_id : null;
        $formId      = $request->filled('form_id') ? (int) $request->form_id : null;
        $grade       = $request->filled('grade') ? (int) $request->grade : null;

        if ($riceType && $riceNameId) {
            $nameType = \App\RiceName::where('id', $riceNameId)->value('type');
            if ($nameType && $nameType !== $riceType) {
                return redirect()->route('user-interests', $request->except(['rice_name_id', 'form_id', 'grade']))
                    ->with('error', 'Selected rice name does not match rice type.');
            }
        }

        $riceNameMeta = null;
        if ($riceNameId) {
            $rn = \App\RiceName::find($riceNameId);
            if ($rn) {
                $riceNameMeta = ['id' => $rn->id, 'name' => $rn->name, 'type' => $rn->type];
            }
        }

        $users = User::query()
            ->where('user_from', 'web')
            ->whereExists(function ($q) use ($riceType, $riceNameId, $formId, $grade) {
                $q->selectRaw('1')
                    ->from('user_interested_map_table as uim')
                    ->whereColumn('uim.user_id', 'users.id')
                    ->where('uim.status', 1);

                if ($riceType) {
                    $q->join('rice_names as rn', 'rn.id', '=', 'uim.rice_name_id')
                        ->where('rn.type', $riceType);
                }
                if ($riceNameId) {
                    $q->where('uim.rice_name_id', $riceNameId);
                }
                if ($formId) {
                    $q->where('uim.form_id', $formId);
                }
                if ($grade !== null) {
                    $q->where('uim.grade', $grade);
                }
            })
            ->orderBy('users.name')
            ->with([
                'interestedMaps' => function ($q) use ($riceType, $riceNameId, $formId, $grade) {
                    $q->where('status', 1)
                        ->when($riceNameId, fn ($qq) => $qq->where('rice_name_id', $riceNameId))
                        ->when($formId, fn ($qq) => $qq->where('form_id', $formId))
                        ->when($grade !== null, fn ($qq) => $qq->where('grade', $grade))
                        ->when($riceType, function ($qq) use ($riceType) {
                            $qq->whereHas('riceName', fn ($r) => $r->where('type', $riceType));
                        })
                        ->orderByDesc('id')
                        ->limit(80)
                        ->with(['riceName', 'riceForm', 'wandGrade.getWandType']);
                },
            ])
            ->paginate(20)
            ->appends($request->query());

        return view('user-interests.index', compact('users', 'riceType', 'riceNameId', 'formId', 'grade', 'riceNameMeta'));
    }

    /**
     * Form options from web_rice_form_map for a rice name (optionally scoped by rice_type).
     */
    public function ajaxFormsByRiceName(Request $request)
    {
        $request->validate([
            'rice_name_id' => 'required|integer|exists:rice_names,id',
            'rice_type'    => 'nullable|in:basmati,non-basmati',
        ]);

        $riceNameId = (int) $request->rice_name_id;
        $riceType   = $request->input('rice_type');

        $maps = WebRiceFormMap::query()
            ->where('rice_name_id', $riceNameId)
            ->when($riceType, function ($q) use ($riceType) {
                $q->where(function ($q2) use ($riceType) {
                    $q2->where('rice_type', $riceType)->orWhereNull('rice_type');
                });
            })
            ->get(['form_ids']);

        $formIdSet = [];
        foreach ($maps as $map) {
            foreach ($this->normalizeFormIdsFromMap($map->form_ids) as $fid) {
                $formIdSet[$fid] = true;
            }
        }
        $ids = array_keys($formIdSet);
        if ($ids === []) {
            return response()->json([]);
        }

        $labels = RiceFormMilestone3::query()
            ->where('status', 1)
            ->whereIn('id', $ids)
            ->orderBy('order')
            ->orderBy('id')
            ->pluck('name', 'id');

        return response()->json($labels);
    }

    /**
     * Wands allowed for rice_name + form from web_rice_form_map (same rules as portal API).
     */
    public function ajaxWandsByRiceForm(Request $request)
    {
        $request->validate([
            'rice_name_id' => 'required|integer|exists:rice_names,id',
            'form_id'      => 'required|integer|exists:rice_form_milestone3,id',
        ]);

        $riceNameId = (int) $request->rice_name_id;
        $formId     = (int) $request->form_id;

        $formMap = WebRiceFormMap::where('rice_name_id', $riceNameId)
            ->where(function ($q) use ($formId) {
                $q->whereJsonContains('form_ids', $formId)
                    ->orWhereJsonContains('form_ids', (string) $formId)
                    ->orWhereRaw('CAST(form_ids AS UNSIGNED) = ?', [$formId]);
            })
            ->first();

        if (!$formMap || !$formMap->wand_ids) {
            return response()->json([]);
        }

        $wands = WandModel::with('getWandType')
            ->whereIn('id', $formMap->wand_ids)
            ->where('status', 1)
            ->orderBy('order')
            ->get()
            ->mapWithKeys(function ($wand) {
                $label = $wand->getWandType ? $wand->getWandType->type . ' - ' . $wand->value : $wand->value;

                return [$wand->id => $label];
            });

        return response()->json($wands);
    }

    /**
     * @param  mixed  $formIds
     * @return int[]
     */
    private function normalizeFormIdsFromMap($formIds): array
    {
        if ($formIds === null || $formIds === '') {
            return [];
        }
        if (is_array($formIds)) {
            $out = [];
            foreach ($formIds as $v) {
                if (is_numeric($v)) {
                    $out[] = (int) $v;
                }
            }

            return array_values(array_unique($out));
        }
        if (is_numeric($formIds)) {
            return [(int) $formIds];
        }
        if (is_string($formIds)) {
            $decoded = json_decode($formIds, true);
            if (is_array($decoded)) {
                return $this->normalizeFormIdsFromMap($decoded);
            }
            if (is_numeric($formIds)) {
                return [(int) $formIds];
            }
        }

        return [];
    }
}
