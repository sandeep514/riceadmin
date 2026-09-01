<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorPackingTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vendor_packing_types', 'name')->ignore($id),
            ],
            'description' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ];
    }
}
