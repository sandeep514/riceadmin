<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoadingReportRequest extends FormRequest
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
        return [
            'sntc_no' => 'required',
            'length' => 'required',
            'ad_mixture' => 'required',
            'moisture' => 'required',
            'kett' => 'required',
            'broken' => 'required',
            'dd' => 'required',
        ];
    }
}
