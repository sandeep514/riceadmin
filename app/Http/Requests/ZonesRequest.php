<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ZonesRequest extends FormRequest
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
            'zone_area' => 'required',
            'city' => 'required'
        ];
        if(request()->city == 0){
            $rules['other_city'] = 'required';
        }
        return $rules;
    }
}
