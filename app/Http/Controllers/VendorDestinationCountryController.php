<?php

namespace App\Http\Controllers;

use App\VendorDestinationCountry;
use App\VendorDestinationRegion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Session;

class VendorDestinationCountryController extends Controller
{
    public function index()
    {
        $records = VendorDestinationCountry::with('region')
            ->orderByDesc('id')
            ->get();

        return view('vendor-destination-countries.index', compact('records'));
    }

    public function create()
    {
        $regions = VendorDestinationRegion::options();

        return view('vendor-destination-countries.create', compact('regions'));
    }

    public function save(Request $request)
    {
        VendorDestinationCountry::create($this->validatePayload($request));
        Session::flash('success', 'Success|Destination country saved successfully!');

        return redirect()->route('vendor-destination-countries');
    }

    public function edit($id)
    {
        $model = VendorDestinationCountry::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $regions = VendorDestinationRegion::options((int) $model->region_id);

        return view('vendor-destination-countries.edit', compact('model', 'regions'));
    }

    public function update(Request $request, $id)
    {
        $model = VendorDestinationCountry::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($this->validatePayload($request, (int) $id));
        Session::flash('success', 'Success|Destination country updated successfully!');

        return redirect()->route('vendor-destination-countries');
    }

    public function delete($id)
    {
        $model = VendorDestinationCountry::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|Destination country deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = VendorDestinationCountry::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === VendorDestinationCountry::STATUS_ACTIVE
            ? VendorDestinationCountry::STATUS_INACTIVE
            : VendorDestinationCountry::STATUS_ACTIVE;
        $model->save();

        Session::flash(
            'success',
            (int) $model->status === VendorDestinationCountry::STATUS_ACTIVE
                ? 'Success|Destination country marked as active.'
                : 'Success|Destination country marked as inactive.'
        );

        return back();
    }

    /**
     * @return array{region_id:int, name:string, description:?string, status:int}
     */
    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'region_id' => 'required|integer|exists:vendor_destination_regions,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vendor_destination_countries', 'name')
                    ->where(fn ($query) => $query->where('region_id', (int) $request->input('region_id')))
                    ->ignore($ignoreId),
            ],
            'description' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ]);

        return [
            'region_id' => (int) $validated['region_id'],
            'name' => trim((string) $validated['name']),
            'description' => isset($validated['description']) ? trim((string) $validated['description']) : null,
            'status' => (int) $validated['status'],
        ];
    }
}
