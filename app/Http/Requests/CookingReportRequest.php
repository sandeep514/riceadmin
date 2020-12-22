<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CookingReportRequest extends FormRequest
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
        if(request()->isMethod('PUT')){
            return [
                'sntc_no' => 'required',
                'remarks' => 'required'
            ];
        }else{
            return [
                'sntc_no' => 'required',
                'remarks' => 'required',
                'image' => 'required'
            ];
        }
    }
}
