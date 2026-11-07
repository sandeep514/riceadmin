<?php

namespace App\Http\Requests;

use App\AnalyserAccount;
use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * Resolve analyser_accounts.id from the route/URL.
     */
    private function resolveAccountId(): ?int
    {
        $raw = $this->route('id');

        if (($raw === null || $raw === '') && $this->route()) {
            $raw = $this->route()->parameter('id');
        }

        // Fallback: .../analyser-accounts/{id}
        if (($raw === null || $raw === '') && preg_match('#analyser-accounts/(\d+)#', $this->path(), $m)) {
            $raw = $m[1];
        }

        $id = is_numeric($raw) ? (int) $raw : 0;

        return $id > 0 ? $id : null;
    }

    /**
     * users.id to ignore for unique email (must be the linked analyser user).
     */
    private function resolveIgnoreUserId(?int $accountId): ?int
    {
        $fromAccount = null;
        if ($accountId) {
            $fromAccount = AnalyserAccount::query()->whereKey($accountId)->value('user_id');
            $fromAccount = $fromAccount ? (int) $fromAccount : null;
        }

        // Prefer DB-linked user_id; accept posted user_id only when it matches (or account missing).
        $fromInput = $this->input('user_id');
        $fromInput = is_numeric($fromInput) ? (int) $fromInput : null;
        if ($fromInput && $fromInput > 0) {
            if ($fromAccount === null || $fromAccount === $fromInput) {
                return $fromInput;
            }
        }

        return $fromAccount;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $accountId = $this->resolveAccountId();
        $ignoreUserId = $this->resolveIgnoreUserId($accountId);

        $isUpdate = $accountId !== null && (
            $this->isMethod('put')
            || $this->isMethod('patch')
            || $this->routeIs('analyser-accounts.update')
        );

        $emailRules = ['required', 'email', 'max:255'];

        $submittedEmail = strtolower(trim((string) $this->input('email', '')));
        $currentEmail = null;
        if ($ignoreUserId) {
            $currentEmail = User::query()->whereKey($ignoreUserId)->value('email');
            $currentEmail = $currentEmail !== null ? strtolower(trim((string) $currentEmail)) : null;
        }

        // Keeping this user's own email — do not run unique (also covers duplicate rows).
        $emailUnchanged = $ignoreUserId
            && $currentEmail !== null
            && $currentEmail === $submittedEmail;

        if (! $emailUnchanged) {
            // Same pattern as UserRequest: unique:users,email,{userId}
            if ($ignoreUserId) {
                $emailRules[] = 'unique:users,email,'.$ignoreUserId;
            } else {
                $emailRules[] = 'unique:users,email';
            }
        }

        return [
            'name' => 'required|string|max:255',
            'email' => $emailRules,
            'user_id' => 'nullable|integer',
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
            'business.selected_category' => [
                'nullable',
                'string',
                'max:256',
                Rule::exists('category', 'id')->where(function ($query) {
                    $categoryIds = AnalyserAccount::allowedCategoryIds();
                    if ($categoryIds === []) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->whereIn('id', $categoryIds)->where(function ($q) {
                        $q->where('status', 1)->orWhereNull('status');
                    });
                }),
            ],
            'business.status' => 'nullable|in:0,1',
            'business.is_sntc_recommended' => 'nullable|in:0,1',
            'business.is_active_listing' => 'nullable|in:0,1',
        ];
    }
}
