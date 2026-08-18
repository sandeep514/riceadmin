<?php

namespace App\Http\Requests;

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

        return [
            'specification' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vendor_specifications', 'specification')
                    ->where(function ($query) {
                        return $query->where('spec_for', $this->input('spec_for'));
                    })
                    ->ignore($id),
            ],
            'description' => 'nullable|string',
            'spec_for' => 'required|string|in:'.implode(',', array_keys(VendorSpecification::specForOptions())),
            'status' => 'required|integer|in:0,1',
        ];
    }

    public function messages()
    {
        return [
            'specification.unique' => 'This specification already exists for the selected Spec For.',
        ];
    }
}
