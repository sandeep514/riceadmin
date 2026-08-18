<?php

namespace App\Http\Requests;

use App\VendorSpecFor;
use App\VendorSpecification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorSpecificationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id');
        $currentSpecForId = null;
        if ($id) {
            $currentSpecForId = VendorSpecification::where('id', $id)->value('spec_for_id');
        }

        return [
            'specification' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vendor_specifications', 'specification')
                    ->where(function ($query) {
                        return $query->where('spec_for_id', $this->input('spec_for_id'));
                    })
                    ->ignore($id),
            ],
            'description' => 'nullable|string',
            'spec_for_id' => [
                'required',
                'integer',
                Rule::exists('vendor_spec_fors', 'id')->where(function ($query) use ($currentSpecForId) {
                    $query->where('status', VendorSpecFor::STATUS_ACTIVE);
                    if ($currentSpecForId) {
                        $query->orWhere('id', $currentSpecForId);
                    }
                }),
            ],
            'status' => 'required|integer|in:0,1',
        ];
    }

    public function messages()
    {
        return [
            'specification.unique' => 'This specification already exists for the selected Spec For.',
            'spec_for_id.required' => 'Please select Spec For.',
            'spec_for_id.exists' => 'Selected Spec For is invalid or inactive.',
        ];
    }
}
