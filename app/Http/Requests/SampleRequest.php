<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SampleRequest extends FormRequest
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
        $rulesArray = [
            'date' => 'required',
            'supplier' => 'required',
            'quality' => 'required',
            'packing' => 'required',
            'packing_type' => 'required',
            'qty' => 'required',
            'no_of_bags' => 'required'
        ];

        if(request()->no_of_bags == 'manual'){
            $rulesArray['bags_qty'] = 'required';
        }
        return $rulesArray;
    }
}
