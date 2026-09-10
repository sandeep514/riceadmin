<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Session;

abstract class AbstractVendorNameMasterController extends Controller
{
    abstract protected function modelClass(): string;

    abstract protected function viewFolder(): string;

    abstract protected function indexRoute(): string;

    abstract protected function label(): string;

    abstract protected function nameColumn(): string;

    public function index()
    {
        $modelClass = $this->modelClass();
        $records = $modelClass::query()->orderByDesc('id')->get();

        return view($this->viewFolder().'.index', compact('records'));
    }

    public function create()
    {
        return view($this->viewFolder().'.create');
    }

    public function save(Request $request)
    {
        $validated = $this->validatePayload($request);
        $modelClass = $this->modelClass();
        $modelClass::create($validated);

        Session::flash('success', 'Success|'.$this->label().' saved successfully!');

        return redirect()->route($this->indexRoute());
    }

    public function edit($id)
    {
        $modelClass = $this->modelClass();
        $model = $modelClass::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        return view($this->viewFolder().'.edit', compact('model'));
    }

    public function update(Request $request, $id)
    {
        $modelClass = $this->modelClass();
        $model = $modelClass::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($this->validatePayload($request, (int) $id));
        Session::flash('success', 'Success|'.$this->label().' updated successfully!');

        return redirect()->route($this->indexRoute());
    }

    public function delete($id)
    {
        $modelClass = $this->modelClass();
        $model = $modelClass::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|'.$this->label().' deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $modelClass = $this->modelClass();
        /** @var Model $model */
        $model = $modelClass::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $active = defined($modelClass.'::STATUS_ACTIVE') ? $modelClass::STATUS_ACTIVE : 1;
        $inactive = defined($modelClass.'::STATUS_INACTIVE') ? $modelClass::STATUS_INACTIVE : 0;

        $model->status = (int) $model->status === $active ? $inactive : $active;
        $model->save();

        Session::flash(
            'success',
            (int) $model->status === $active
                ? 'Success|'.$this->label().' marked as active.'
                : 'Success|'.$this->label().' marked as inactive.'
        );

        return back();
    }

    /**
     * @return array{name:string, description:?string, status:int}
     */
    protected function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $nameCol = $this->nameColumn();
        $table = (new ($this->modelClass()))->getTable();

        $validated = $request->validate([
            $nameCol => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique($table, $nameCol)->ignore($ignoreId),
            ],
            'description' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ], [], [
            $nameCol => strtolower($this->label()),
        ]);

        return [
            $nameCol => trim((string) $validated[$nameCol]),
            'description' => isset($validated['description']) ? trim((string) $validated['description']) : null,
            'status' => (int) $validated['status'],
        ];
    }
}
