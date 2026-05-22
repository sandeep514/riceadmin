<?php

namespace App\Http\Controllers;

use App\RiceFormMilestone3;
use App\RiceFormParentMap;
use Illuminate\Http\Request;
use Session;

class RiceFormParentMapController extends Controller
{
    public function index()
    {
        $records = RiceFormParentMap::with('parentForm')
            ->orderByDesc('id')
            ->get();

        return view('rice-form-parent-map.index', compact('records'));
    }

    public function create()
    {
        $riceForms = $this->activeRiceForms();

        return view('rice-form-parent-map.create', compact('riceForms'));
    }

    public function save(Request $request)
    {
        $data = $this->validated($request);

        if ($this->parentAlreadyMapped($data['parent_form_id'])) {
            Session::flash('error', 'Error|This parent form is already mapped.');

            return back()->withInput();
        }

        if ($conflict = $this->childConflictMessage($data['child_form_ids'])) {
            Session::flash('error', 'Error|'.$conflict);

            return back()->withInput();
        }

        RiceFormParentMap::create($data);

        Session::flash('success', 'Success|Parent–child form map saved successfully!');

        return redirect()->route('rice-form-parent-map');
    }

    public function edit($id)
    {
        $model = RiceFormParentMap::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $riceForms = $this->activeRiceForms();

        return view('rice-form-parent-map.edit', compact('model', 'riceForms'));
    }

    public function update(Request $request, $id)
    {
        $model = RiceFormParentMap::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $data = $this->validated($request);

        if ($this->parentAlreadyMapped($data['parent_form_id'], (int) $id)) {
            Session::flash('error', 'Error|This parent form is already mapped.');

            return back()->withInput();
        }

        if ($conflict = $this->childConflictMessage($data['child_form_ids'], (int) $id)) {
            Session::flash('error', 'Error|'.$conflict);

            return back()->withInput();
        }

        $model->update($data);

        Session::flash('success', 'Success|Map updated successfully!');

        return redirect()->route('rice-form-parent-map');
    }

    public function delete($id)
    {
        $model = RiceFormParentMap::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');
        } else {
            $model->delete();
            Session::flash('success', 'Success|Record deleted successfully!');
        }

        return back();
    }

    private function activeRiceForms()
    {
        return RiceFormMilestone3::where('status', 1)
            ->orderBy('order')
            ->pluck('name', 'id');
    }

    private function validated(Request $request): array
    {
        $request->validate([
            'parent_form_id' => 'required|exists:rice_form_milestone3,id',
            'child_form_ids' => 'required|array|min:1',
            'child_form_ids.*' => 'exists:rice_form_milestone3,id',
            'status' => 'nullable|in:0,1',
        ]);

        $parentId = (int) $request->parent_form_id;
        $childIds = array_values(array_unique(array_map('intval', $request->child_form_ids ?? [])));

        if (in_array($parentId, $childIds, true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'child_form_ids' => ['Parent form cannot be selected as a child.'],
            ]);
        }

        return [
            'parent_form_id' => $parentId,
            'child_form_ids' => $childIds,
            'status' => (int) ($request->status ?? 1),
        ];
    }

    private function parentAlreadyMapped(int $parentFormId, ?int $exceptId = null): bool
    {
        $q = RiceFormParentMap::where('parent_form_id', $parentFormId);

        if ($exceptId !== null) {
            $q->where('id', '!=', $exceptId);
        }

        return $q->exists();
    }

    private function childConflictMessage(array $childIds, ?int $exceptId = null): ?string
    {
        $others = RiceFormParentMap::query()
            ->when($exceptId !== null, fn ($q) => $q->where('id', '!=', $exceptId))
            ->get();

        foreach ($others as $row) {
            $overlap = array_intersect($childIds, $row->child_form_ids ?? []);
            if ($overlap !== []) {
                $parentName = $row->parentForm?->name ?? 'another parent';

                return 'One or more child forms are already mapped under "'.$parentName.'".';
            }
        }

        return null;
    }
}
