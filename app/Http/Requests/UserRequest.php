<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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

        if(request()->method == 'PUT'){
            if(request()->role == 2){
                return [
                    'name' => 'required',
                    'email' => 'required|unique:users,email,'.request()->id,
                    'mobile' => 'required'
                ];
            }
            if(request()->role == 3){
                return [
                    'name' => 'required',
//                    'email' => 'required|unique:users,email,'.request()->id,
//                    'address' => 'required',
                    'mobile' => 'required',
                    'designation' => 'required',
                    'zone' => 'required'
                ];
            }
            if(request()->role == 4 || request()->role == 5){
                return [
                    'name' => 'required',
                    'email' => 'required|unique:users,email,'.request()->id,
                    'mobile' => 'required',
                    'state' => 'required',
                    'city' => 'required',
                    'company' => 'required',
                    'contact_person' => 'required'
                ];
            }
        }else{
            if(request()->role == 2){
                return [
                    'name' => 'required',
                    'email' => 'required|unique:users',
                    'password' => 'required',
                    'mobile' => 'required'
                ];
            }
            if(request()->role == 3){
                return [
                    'name' => 'required',
//                    'email' => 'required|unique:users',
                    'password' => 'required',
                    'mobile' => 'required',
                    'designation' => 'required',
                    'zone' => 'required'
                ];
            }
            if(request()->role == 4 || request()->role == 5){
                return [
                    'name' => 'required',
                    'email' => 'required|unique:users',
                    'mobile' => 'required',
                    'state' => 'required',
                    'city' => 'required',
                    'company' => 'required',
                    'contact_person' => 'required',
                    'password' => 'required'
                ];
            }
        }
    }
}
