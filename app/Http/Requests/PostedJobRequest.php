<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostedJobRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:500',
            'description' => 'required|string',
            'job_role' => 'nullable|string|max:500',
            'location' => 'required|string|max:255',
            'employment_type' => 'required|in:fulltime,parttime',
            'last_date_apply' => 'required|date',
            'number_of_positions' => 'required|integer|min:1|max:99999',
        ];
    }
}
