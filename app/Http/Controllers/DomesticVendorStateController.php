<?php

namespace App\Http\Controllers;

use App\DomesticVendorCountry;
use App\DomesticVendorState;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Session;

class DomesticVendorStateController extends Controller
{
    public function index()
    {
        $records = DomesticVendorState::with('country')->orderByDesc('id')->get();

        return view('domestic-vendor-states.index', compact('records'));
    }

    public function create()
    {
        $countries = DomesticVendorCountry::options();

        return view('domestic-vendor-states.create', compact('countries'));
    }

    public function save(Request $request)
    {
        DomesticVendorState::create($this->validatePayload($request));
        Session::flash('success', 'Success|State saved successfully!');

        return redirect()->route('domestic-vendor-states');
    }

    public function edit($id)
    {
        $model = DomesticVendorState::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $countries = DomesticVendorCountry::options((int) $model->country_id);

        return view('domestic-vendor-states.edit', compact('model', 'countries'));
    }

    public function update(Request $request, $id)
    {
        $model = DomesticVendorState::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($this->validatePayload($request, (int) $id));
        Session::flash('success', 'Success|State updated successfully!');

        return redirect()->route('domestic-vendor-states');
    }

    public function delete($id)
    {
        $model = DomesticVendorState::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        if ($model->cities()->exists()) {
            Session::flash('error', 'Error|Remove cities under this state first.');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|State deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = DomesticVendorState::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === DomesticVendorState::STATUS_ACTIVE
            ? DomesticVendorState::STATUS_INACTIVE
            : DomesticVendorState::STATUS_ACTIVE;
        $model->save();

        Session::flash(
            'success',
            (int) $model->status === DomesticVendorState::STATUS_ACTIVE
                ? 'Success|State marked as active.'
                : 'Success|State marked as inactive.'
        );

        return back();
    }

    /**
     * @return array{country_id:int, name:string, description:?string, status:int}
     */
    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'country_id' => 'required|integer|exists:domestic_vendor_countries,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('domestic_vendor_states', 'name')
                    ->where(fn ($query) => $query->where('country_id', (int) $request->input('country_id')))
                    ->ignore($ignoreId),
            ],
            'description' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ]);

        return [
            'country_id' => (int) $validated['country_id'],
            'name' => trim((string) $validated['name']),
            'description' => isset($validated['description']) ? trim((string) $validated['description']) : null,
            'status' => (int) $validated['status'],
        ];
    }
}
