<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentsFormRequest extends FormRequest
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
        if(request()->method('PUT')){
            return [
                'date' => 'required',
                'contract_no' => 'required',
                'truck_no' => 'required',
                'driver_no' => 'required',
                'due_days' => 'required',
                'due_date' => 'required'
            ];
        }else{
            return [
                'date' => 'required',
                'contract_no' => 'required',
                'truck_no' => 'required',
                'driver_no' => 'required',
                'contract_copy' => 'required',
                'bill_copy' => 'required',
                'bilty_copy' => 'required',
                'kanta_parchi' => 'required',
                'due_days' => 'required',
                'due_date' => 'required'
            ];
        }
    }
}
