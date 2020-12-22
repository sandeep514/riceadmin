<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DealFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'date' => 'required',
            'contract_no' => 'required',
            'buyer' => 'required',
            'seller' => 'required',
            'quantity' => 'required',
        ];
        if(!request()->has('is_direct_deal')){
            $rules['sntc_no'] = 'required';
        }
        return $rules;
    }
}
