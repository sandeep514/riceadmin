<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WebSideMenuRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'sub_title' => 'nullable|string|max:255',
            'create_url' => 'nullable|string|max:255',
            'read_url' => 'nullable|string|max:255',
            'update_url' => 'nullable|string|max:255',
            'delete_url' => 'nullable|string|max:255',
            'status' => 'nullable|integer|in:0,1',
            'sort_order' => 'nullable|integer|min:0'
        ];
    }
}

