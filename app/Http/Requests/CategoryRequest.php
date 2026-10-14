<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id');

        return [
            'category' => [
                'required',
                'string',
                'max:255',
                Rule::unique('category', 'category')->ignore($id),
            ],
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'status' => 'required|integer|in:0,1',
        ];
    }
}
