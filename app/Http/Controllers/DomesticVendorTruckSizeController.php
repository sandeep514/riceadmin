<?php

namespace App\Http\Controllers;

use App\DomesticVendorTruckSize;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Session;

class DomesticVendorTruckSizeController extends Controller
{
    public function index()
    {
        $records = DomesticVendorTruckSize::query()->orderBy('size')->orderByDesc('id')->get();

        return view('domestic-vendor-truck-sizes.index', compact('records'));
    }

    public function create()
    {
        return view('domestic-vendor-truck-sizes.create');
    }

    public function save(Request $request)
    {
        DomesticVendorTruckSize::create($this->validatePayload($request));
        Session::flash('success', 'Success|Truck size saved successfully!');

        return redirect()->route('domestic-vendor-truck-sizes');
    }

    public function edit($id)
    {
        $model = DomesticVendorTruckSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        return view('domestic-vendor-truck-sizes.edit', compact('model'));
    }

    public function update(Request $request, $id)
    {
        $model = DomesticVendorTruckSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($this->validatePayload($request, (int) $id));
        Session::flash('success', 'Success|Truck size updated successfully!');

        return redirect()->route('domestic-vendor-truck-sizes');
    }

    public function delete($id)
    {
        $model = DomesticVendorTruckSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|Truck size deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = DomesticVendorTruckSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === DomesticVendorTruckSize::STATUS_ACTIVE
            ? DomesticVendorTruckSize::STATUS_INACTIVE
            : DomesticVendorTruckSize::STATUS_ACTIVE;
        $model->save();

        Session::flash(
            'success',
            (int) $model->status === DomesticVendorTruckSize::STATUS_ACTIVE
                ? 'Success|Truck size marked as active.'
                : 'Success|Truck size marked as inactive.'
        );

        return back();
    }

    /**
     * @return array{size:float, label:string, description:?string, status:int}
     */
    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'size' => [
                'required',
                'numeric',
                'min:0.01',
                'max:99999',
                Rule::unique('domestic_vendor_truck_sizes', 'size')->ignore($ignoreId),
            ],
            'label' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ]);

        $size = round((float) $validated['size'], 2);
        $label = isset($validated['label']) ? trim((string) $validated['label']) : '';
        if ($label === '') {
            $label = rtrim(rtrim(number_format($size, 2, '.', ''), '0'), '.').' MT';
        }

        return [
            'size' => $size,
            'label' => $label,
            'description' => isset($validated['description']) ? trim((string) $validated['description']) : null,
            'status' => (int) $validated['status'],
        ];
    }
}
