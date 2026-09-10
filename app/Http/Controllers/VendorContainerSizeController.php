<?php

namespace App\Http\Controllers;

use App\VendorContainerSize;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Session;

class VendorContainerSizeController extends Controller
{
    public function index()
    {
        $records = VendorContainerSize::query()->orderBy('size')->orderByDesc('id')->get();

        return view('vendor-container-sizes.index', compact('records'));
    }

    public function create()
    {
        return view('vendor-container-sizes.create');
    }

    public function save(Request $request)
    {
        VendorContainerSize::create($this->validatePayload($request));
        Session::flash('success', 'Success|Container size saved successfully!');

        return redirect()->route('vendor-container-sizes');
    }

    public function edit($id)
    {
        $model = VendorContainerSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        return view('vendor-container-sizes.edit', compact('model'));
    }

    public function update(Request $request, $id)
    {
        $model = VendorContainerSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->update($this->validatePayload($request, (int) $id));
        Session::flash('success', 'Success|Container size updated successfully!');

        return redirect()->route('vendor-container-sizes');
    }

    public function delete($id)
    {
        $model = VendorContainerSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->delete();
        Session::flash('success', 'Success|Container size deleted successfully!');

        return back();
    }

    public function changeStatus($id)
    {
        $model = VendorContainerSize::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $model->status = (int) $model->status === VendorContainerSize::STATUS_ACTIVE
            ? VendorContainerSize::STATUS_INACTIVE
            : VendorContainerSize::STATUS_ACTIVE;
        $model->save();

        Session::flash(
            'success',
            (int) $model->status === VendorContainerSize::STATUS_ACTIVE
                ? 'Success|Container size marked as active.'
                : 'Success|Container size marked as inactive.'
        );

        return back();
    }

    /**
     * @return array{size:int, label:?string, description:?string, status:int}
     */
    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'size' => [
                'required',
                'integer',
                'min:1',
                'max:999',
                Rule::unique('vendor_container_sizes', 'size')->ignore($ignoreId),
            ],
            'label' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ]);

        $size = (int) $validated['size'];
        $label = isset($validated['label']) ? trim((string) $validated['label']) : '';
        if ($label === '') {
            $label = $size.' FT';
        }

        return [
            'size' => $size,
            'label' => $label,
            'description' => isset($validated['description']) ? trim((string) $validated['description']) : null,
            'status' => (int) $validated['status'],
        ];
    }
}
