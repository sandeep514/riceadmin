<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Role;

class WebAccessRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'role_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $role = Role::where('id', $value)->where('type', 'web')->first();
                    if (!$role) {
                        $fail('The selected role must be a web role.');
                    }
                }
            ],
            'category_id' => 'nullable|exists:category,id',
            'plan_id' => 'nullable|exists:web_plan,id',
            'menu_permissions' => 'required|array',
            'menu_permissions.*.can_create' => 'nullable|boolean',
            'menu_permissions.*.can_read' => 'nullable|boolean',
            'menu_permissions.*.can_update' => 'nullable|boolean',
            'menu_permissions.*.can_delete' => 'nullable|boolean',
            'status' => 'nullable|integer|in:0,1'
        ];
    }
}

