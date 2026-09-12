<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorContainerParticularRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id');

        return [
            'particular' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vendor_container_particulars', 'particular')->ignore($id),
            ],
            'input_type' => [
                'required',
                'string',
                Rule::in(array_keys(\App\VendorContainerParticular::inputTypeOptions())),
            ],
            'description' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ];
    }
}
