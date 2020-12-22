<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SampleOutwardRequest extends FormRequest
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
            'sntc_no' => 'required',
            'buyer' => 'required',
            'quality' => 'required',
            'bag_type' => 'required',
            'no_of_bags' => 'required',
            'awb_no' => 'required'
        ];
        if(request()->no_of_bags == 'manual'){
            $rules['qty_per_bag'] =  'required';
            $rules['qty'] = 'required';
        }
        return $rules;
    }
}
