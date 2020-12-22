<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SampleLabReportRequest extends FormRequest
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
            'sntc_no' => 'required',
            'length' => 'required',
            'ad_mixture' => 'required',
            'moisture' => 'required',
            'kett' => 'required',
            'broken' => 'required',
            'dd' => 'required',
            'chalky' => 'required',
            'brown_layer' => 'required',
            'stone' => 'required',
            'inmature' => 'required',
            'broken_pin' => 'required',
            'cooking' => 'required'
        ];

        return $rules;
    }
}
