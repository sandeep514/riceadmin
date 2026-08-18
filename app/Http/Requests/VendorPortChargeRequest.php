<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorPortChargeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id');

        return [
            'charge' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vendor_port_charges', 'charge')->ignore($id),
            ],
            'description' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ];
    }
}
