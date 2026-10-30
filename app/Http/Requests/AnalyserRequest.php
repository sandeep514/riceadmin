<?php

namespace App\Http\Requests;

use App\AnalyserAccount;
use App\CategoryRoleMap;
use App\WebPlanModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AnalyserRequest extends FormRequest
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
        $emailRule = 'required|email|max:255|unique:users,email';
        if (in_array($this->method(), ['PUT', 'PATCH']) && $this->route('id')) {
            $account = AnalyserAccount::find($this->route('id'));
            $userId = $account ? $account->user_id : 0;
            $emailRule = 'required|email|max:255|unique:users,email,'.$userId;
        }

        $isUpdate = in_array($this->method(), ['PUT', 'PATCH'], true);
        $roleId = AnalyserAccount::analyserRoleId();

        return [
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'password' => $isUpdate
                ? 'nullable|string|min:6|confirmed'
                : 'required|string|min:6|confirmed',
            'password_confirmation' => $isUpdate
                ? 'nullable|string|min:6'
                : 'required|string|min:6',
            'contact_person_name' => 'required|string|max:255',
            'contact_mobile' => 'required|string|max:20',
            'category_id' => [
                'required',
                'integer',
                Rule::exists('category', 'id')->where(function ($query) use ($roleId) {
                    if (! $roleId) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $mapQuery = CategoryRoleMap::query()->where('role', $roleId);
                    if (Schema::hasColumn('category_role_map', 'status')) {
                        $mapQuery->where(function ($q) {
                            $q->where('status', 1)->orWhereNull('status');
                        });
                    }
                    $categoryIds = $mapQuery->pluck('category')->all();
                    $planCategoryIds = WebPlanModel::query()
                        ->where('role_id', $roleId)
                        ->whereNotNull('category_id')
                        ->pluck('category_id')
                        ->all();
                    $allowed = array_values(array_unique(array_merge($categoryIds, $planCategoryIds)));
                    if ($allowed === []) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $query->whereIn('id', $allowed);
                    }
                }),
            ],
            'plan_id' => [
                'required',
                'integer',
                Rule::exists('web_plan', 'id')->where(function ($query) use ($roleId) {
                    $query->where(function ($q) {
                        $q->where('status', 1)->orWhereNull('status');
                    });
                    if ($roleId) {
                        $query->where('role_id', $roleId);
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                    if ($this->filled('category_id')) {
                        $query->where('category_id', (int) $this->input('category_id'));
                    }
                }),
            ],
            'subscription_type' => 'required|string|in:monthly,half_yearly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'received_amount' => 'required|numeric|min:0',
            'has_historical_access' => 'required|boolean',
            'download_limit' => 'nullable|integer|min:1',
            'has_today_access' => 'required|boolean',
            'send_mail' => 'sometimes|boolean',

            'business' => 'nullable|array',
            'business.company_name' => 'nullable|string|max:256',
            'business.product' => 'nullable|string|max:5000',
            'business.contactPerson' => 'nullable|string|max:255',
            'business.contactMobile' => 'nullable|string|max:256',
            'business.designation' => 'nullable|string|max:256',
            'business.address' => 'nullable|string|max:5000',
            'business.registered_email' => 'nullable|email|max:256',
            'business.phone' => 'nullable|string|max:256',
            'business.locality' => 'nullable|string|max:256',
            'business.landmark' => 'nullable|string|max:256',
            'business.state' => 'nullable|string|max:256',
            'business.city' => 'nullable|string|max:256',
            'business.selected_category' => 'nullable|string|max:256',
            'business.status' => 'nullable|in:0,1',
            'business.is_sntc_recommended' => 'nullable|in:0,1',
            'business.is_active_listing' => 'nullable|in:0,1',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $plan = WebPlanModel::query()->find((int) $this->input('plan_id'));
            if (! $plan) {
                return;
            }

            if ((int) $plan->category_id !== (int) $this->input('category_id')) {
                $validator->errors()->add('plan_id', 'Selected plan does not belong to the chosen category.');
            }
        });
    }
}
