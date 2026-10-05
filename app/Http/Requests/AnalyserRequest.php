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

        return [
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'contact_person_name' => 'required|string|max:255',
            'contact_mobile' => 'required|string|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'received_amount' => 'nullable|numeric|min:0',
            'has_historical_access' => 'required|boolean',
            'download_limit' => 'nullable|integer|min:1',
            'has_today_access' => 'required|boolean',
            'send_mail' => 'sometimes|boolean',
        ];
    }
}
