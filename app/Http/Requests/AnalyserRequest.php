<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            $account = \App\AnalyserAccount::find($this->route('id'));
            $userId = $account ? $account->user_id : 0;
            $emailRule = 'required|email|max:255|unique:users,email,' . $userId;
        }

        $isUpdate = in_array($this->method(), ['PUT', 'PATCH'], true);

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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'received_amount' => 'nullable|numeric|min:0',
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
}
