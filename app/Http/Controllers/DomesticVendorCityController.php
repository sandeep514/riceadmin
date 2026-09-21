<?php

namespace App\Http\Controllers;

use App\DomesticVendorCity;
use App\DomesticVendorCountry;
use App\DomesticVendorState;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Session;

class DomesticVendorCityController extends Controller
{
    public function index()
    {
        $records = DomesticVendorCity::with('state.country')->orderByDesc('id')->get();

        return view('domestic-vendor-cities.index', compact('records'));
    }

    public function create()
    {
        $countries = DomesticVendorCountry::options();
        $states = DomesticVendorState::query()
            ->with('country')
            ->orderBy('name')
            ->get(['id', 'country_id', 'name', 'status']);

        return view('domestic-vendor-cities.create', compact('countries', 'states'));
    }

    public function save(Request $request)
    {
        DomesticVendorCity::create($this->validatePayload($request));
        Session::flash('success', 'Success|City saved successfully!');

        return redirect()->route('domestic-vendor-cities');
    }

    public function edit($id)
    {
        $model = DomesticVendorCity::with('state')->find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $countries = DomesticVendorCountry::options((int) optional($model->state)->country_id);
        $states = DomesticVendorState::query()
            ->with('country')
            ->orderBy('name')
            ->get(['id', 'country_id', 'name', 'status']);

        return view('domestic-vendor-cities.edit', compact('model', 'countries', 'states'));
    }

    public function update(Request $request, $id)
    {
        $model = DomesticVendorCity::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($this->validatePayload($request, (int) $id));
        Session::flash('success', 'Success|City updated successfully!');

        return redirect()->route('domestic-vendor-cities');
    }

    public function delete($id)
    {
        $model = DomesticVendorCity::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|City deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = DomesticVendorCity::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === DomesticVendorCity::STATUS_ACTIVE
            ? DomesticVendorCity::STATUS_INACTIVE
            : DomesticVendorCity::STATUS_ACTIVE;
        $model->save();

        Session::flash(
            'success',
            (int) $model->status === DomesticVendorCity::STATUS_ACTIVE
                ? 'Success|City marked as active.'
                : 'Success|City marked as inactive.'
        );

        return back();
    }

    /**
     * @return array{state_id:int, name:string, description:?string, status:int}
     */
    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'state_id' => 'required|integer|exists:domestic_vendor_states,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('domestic_vendor_cities', 'name')
                    ->where(fn ($query) => $query->where('state_id', (int) $request->input('state_id')))
                    ->ignore($ignoreId),
            ],
            'description' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ]);

        return [
            'state_id' => (int) $validated['state_id'],
            'name' => trim((string) $validated['name']),
            'description' => isset($validated['description']) ? trim((string) $validated['description']) : null,
            'status' => (int) $validated['status'],
        ];
    }
}
