<?php

namespace App\Http\Controllers;

use App\AnalyserAccount;
use App\CategoryRoleMap;
use App\Http\Requests\AnalyserRequest;
use App\Role;
use App\Support\QueuedMail;
use App\User;
use App\WebBusinessDetails;
use App\WebCities;
use App\WebPlanModel;
use App\WebStates;
use App\WebUserSubscriptionModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Session;

class AnalyserController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $from = $request->query('from');
        $to = $request->query('to');

        $allowedStatuses = ['active', 'inactive', 'expired', 'upcoming', 'disabled'];
        if (! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        try {
            $fromDate = $from ? Carbon::parse($from)->toDateString() : null;
        } catch (\Exception $e) {
            $fromDate = null;
        }
        try {
            $toDate = $to ? Carbon::parse($to)->toDateString() : null;
        } catch (\Exception $e) {
            $toDate = null;
        }

        $today = Carbon::today()->toDateString();
        $hasDeactivatedColumn = Schema::hasColumn('users', 'is_deactivated');

        $query = AnalyserAccount::with(['user', 'plan'])->orderBy('id', 'desc');

        if ($fromDate) {
            $query->whereDate('analyser_accounts.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('analyser_accounts.created_at', '<=', $toDate);
        }

        if ($status === 'active') {
            $query->whereHas('user', function ($q) use ($hasDeactivatedColumn) {
                $q->where('status', 1);
                if ($hasDeactivatedColumn) {
                    $q->where('is_deactivated', 0);
                }
            })->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhereDate('start_date', '<=', $today);
            })->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
            });
        } elseif ($status === 'inactive') {
            $query->where(function ($q) use ($today, $hasDeactivatedColumn) {
                $q->whereDoesntHave('user')
                    ->orWhereHas('user', function ($uq) use ($hasDeactivatedColumn) {
                        $uq->where('status', '!=', 1);
                        if ($hasDeactivatedColumn) {
                            $uq->orWhere('is_deactivated', 1);
                        }
                    })
                    ->orWhereDate('start_date', '>', $today)
                    ->orWhereDate('end_date', '<', $today);
            });
        } elseif ($status === 'expired') {
            $query->whereDate('end_date', '<', $today);
        } elseif ($status === 'upcoming') {
            $query->whereDate('start_date', '>', $today);
        } elseif ($status === 'disabled') {
            $query->where(function ($q) use ($hasDeactivatedColumn) {
                $q->whereDoesntHave('user')
                    ->orWhereHas('user', function ($uq) use ($hasDeactivatedColumn) {
                        $uq->where('status', '!=', 1);
                        if ($hasDeactivatedColumn) {
                            $uq->orWhere('is_deactivated', 1);
                        }
                    });
            });
        }

        $accounts = $query->get();

        return view('analyser_accounts.index', compact('accounts', 'status', 'fromDate', 'toDate'));
    }

    public function create()
    {
        return view('analyser_accounts.create', $this->formOptions());
    }

    public function store(AnalyserRequest $request)
    {
        $role = Role::firstOrCreate(['role_name' => 'analyser'], ['type' => 'analyser']);
        $otp = random_int(100000, 999999);
        $plainPassword = (string) $request->password;
        $planId = (int) $request->plan_id;

        DB::transaction(function () use ($request, $role, $otp, $plainPassword, $planId) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($plainPassword),
                'contact_person_name' => $request->contact_person_name,
                'mobile' => $request->contact_mobile,
                'role' => $role->id,
                'userType' => 2,
                'user_from' => 'web',
                'status' => 1,
                'is_active_by_admin' => 1,
                'otp' => $otp,
            ]);

            AnalyserAccount::create([
                'user_id' => $user->id,
                'plan_id' => $planId,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'received_amount' => $request->received_amount,
                'contact_person_name' => $request->contact_person_name,
                'contact_mobile' => $request->contact_mobile,
                'has_historical_access' => $request->has_historical_access,
                'download_limit' => $request->download_limit,
                'has_today_access' => $request->has_today_access,
                'created_by' => auth()->id(),
            ]);

            $this->syncBusinessDetails($user->id, $request);
            $this->syncAnalyserSubscription(
                $user->id,
                $planId,
                $request->start_date,
                $request->end_date,
                $request->received_amount
            );
        });

        if ($request->boolean('send_mail')) {
            $this->sendCredentialsMail($request->email, $otp, $plainPassword);
        }

        Session::flash('success', 'Success|Analyser account created successfully!');
        return redirect()->route('analyser-accounts.index');
    }

    public function edit($id)
    {
        $account = AnalyserAccount::with(['user.getWebBusinessDetails', 'plan'])->find($id);
        if ($account == null) {
            Session::flash('error', 'Error|No record found!');
            return back();
        }

        return view('analyser_accounts.edit', array_merge(
            ['model' => $account, 'businessDetails' => optional($account->user)->getWebBusinessDetails],
            $this->formOptions()
        ));
    }

    public function update(AnalyserRequest $request, $id)
    {
        $account = AnalyserAccount::with('user')->find($id);
        if ($account == null) {
            Session::flash('error', 'Error|No record found!');
            return back();
        }

        $otp = null;
        if ($request->boolean('send_mail')) {
            $otp = random_int(100000, 999999);
        }

        $plainPassword = filled($request->password) ? (string) $request->password : null;
        $planId = (int) $request->plan_id;

        DB::transaction(function () use ($request, $account, $otp, $plainPassword, $planId) {
            $userUpdate = [
                'name' => $request->name,
                'email' => $request->email,
                'contact_person_name' => $request->contact_person_name,
                'mobile' => $request->contact_mobile,
            ];
            if ($plainPassword !== null) {
                $userUpdate['password'] = Hash::make($plainPassword);
            }
            if ($otp) {
                $userUpdate['otp'] = $otp;
            }

            $account->user->update($userUpdate);

            $account->update([
                'plan_id' => $planId,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'received_amount' => $request->received_amount,
                'contact_person_name' => $request->contact_person_name,
                'contact_mobile' => $request->contact_mobile,
                'has_historical_access' => $request->has_historical_access,
                'download_limit' => $request->download_limit,
                'has_today_access' => $request->has_today_access,
            ]);

            $this->syncBusinessDetails($account->user_id, $request);
            $this->syncAnalyserSubscription(
                (int) $account->user_id,
                $planId,
                $request->start_date,
                $request->end_date,
                $request->received_amount
            );
        });

        if ($otp) {
            $this->sendCredentialsMail($request->email, $otp, $plainPassword);
        }

        Session::flash('success', 'Success|Analyser account updated successfully!');
        return redirect()->route('analyser-accounts.index');
    }

    public function destroy($id)
    {
        $account = AnalyserAccount::find($id);
        if ($account == null) {
            Session::flash('error', 'Error|No record found!');
            return back();
        }

        DB::transaction(function () use ($account) {
            $referencePrefix = 'analyser_admin_'.$account->user_id;
            WebUserSubscriptionModel::where('user_id', $account->user_id)
                ->where(function ($q) use ($referencePrefix) {
                    $q->where('payment_id', 'like', $referencePrefix.'%')
                        ->orWhere('order_id', 'like', $referencePrefix.'%');
                })
                ->delete();
            WebBusinessDetails::where('user_id', $account->user_id)->delete();
            User::where('id', $account->user_id)->delete();
            $account->delete();
        });

        Session::flash('success', 'Success|Analyser account deleted successfully!');
        return back();
    }

    /**
     * @return array{
     *   categories: array<int|string, string>,
     *   states: array<int|string, string>,
     *   cities: \Illuminate\Support\Collection,
     *   plans: array<int|string, string>,
     *   analyserRoleId: int|null,
     *   analyserRoleName: string
     * }
     */
    private function formOptions(): array
    {
        $analyserRole = Role::firstOrCreate(['role_name' => 'analyser'], ['type' => 'analyser']);
        $analyserRoleId = (int) $analyserRole->id;
        $analyserRoleName = (string) ($analyserRole->role_name ?: 'analyser');

        $categories = $this->analyserCategoryOptions($analyserRoleId);

        $states = WebStates::query()
            ->orderBy('state_name')
            ->pluck('state_name', 'id')
            ->toArray();

        $cities = WebCities::query()
            ->orderBy('city_name')
            ->get(['id', 'city_name', 'state_id']);

        $plans = $this->analyserPlanOptions();

        return compact('categories', 'states', 'cities', 'plans', 'analyserRoleId', 'analyserRoleName');
    }

    /**
     * Categories mapped to the analyser role only.
     *
     * @return array<int|string, string>
     */
    private function analyserCategoryOptions(int $roleId): array
    {
        $mapQuery = CategoryRoleMap::query()
            ->where('role', $roleId)
            ->with(['category_rel' => function ($q) {
                $q->where('status', 1)
                    ->orderByRaw('COALESCE(`order`, 999999)')
                    ->orderBy('category');
            }]);

        if (Schema::hasColumn('category_role_map', 'status')) {
            $mapQuery->where('status', 1);
        }

        $options = [];
        foreach ($mapQuery->get() as $map) {
            if ($map->category_rel) {
                $options[$map->category_rel->id] = $map->category_rel->category;
            }
        }

        return $options;
    }

    /**
     * Active web plans for the analyser role only (no other roles).
     *
     * @return array<int|string, string>
     */
    private function analyserPlanOptions(): array
    {
        $roleId = AnalyserAccount::analyserRoleId();
        if (! $roleId) {
            return [];
        }

        return WebPlanModel::query()
            ->where('status', 1)
            ->where('role_id', $roleId)
            ->orderBy('title')
            ->pluck('title', 'id')
            ->toArray();
    }

    /**
     * Upsert web_user_subscription for the analyser user (same table/shape as other roles).
     */
    private function syncAnalyserSubscription(
        int $userId,
        int $planId,
        string $startDate,
        string $endDate,
        $receivedAmount = null
    ): void {
        $periodStart = Carbon::parse($startDate)->format('Y-m-d');
        $periodEnd = Carbon::parse($endDate)->format('Y-m-d');
        $referencePrefix = 'analyser_admin_'.$userId;

        $amount = null;
        if ($receivedAmount !== null && $receivedAmount !== '') {
            $amount = $receivedAmount;
        } else {
            $plan = WebPlanModel::query()->find($planId);
            if ($plan) {
                $amount = $plan->yearly_final_amount
                    ?? $plan->yearly_price
                    ?? $plan->monthly_final_amount
                    ?? $plan->monthly_price
                    ?? $plan->amount
                    ?? null;
            }
        }

        // Same columns as portal paid/trial subscriptions for other roles.
        $payload = [
            'user_id' => $userId,
            'plan_id' => $planId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            // Custom date range from admin; treat as yearly-style paid access.
            'subscription_type' => 'yearly',
            'amount' => $amount,
            'currency' => 'INR',
            'status' => 1,
        ];

        $existing = WebUserSubscriptionModel::query()
            ->where('user_id', $userId)
            ->where(function ($q) use ($referencePrefix) {
                $q->where('payment_id', 'like', $referencePrefix.'%')
                    ->orWhere('order_id', 'like', $referencePrefix.'%');
            })
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            $existing->update($payload);

            return;
        }

        $reference = $referencePrefix.'_'.time();
        WebUserSubscriptionModel::create(array_merge($payload, [
            'payment_id' => $reference,
            'order_id' => $reference,
        ]));
    }

    private function syncBusinessDetails(int $userId, Request $request): void
    {
        $input = $request->input('business', []);
        if (! is_array($input)) {
            $input = [];
        }

        $fillable = array_flip((new WebBusinessDetails)->getFillable());
        unset($fillable['user_id']);

        $data = array_intersect_key($input, $fillable);

        foreach (['company_name', 'product', 'contactPerson', 'contactMobile', 'designation', 'address', 'registered_email', 'phone', 'locality', 'landmark', 'state', 'city', 'selected_category'] as $key) {
            if (array_key_exists($key, $data) && is_string($data[$key])) {
                $data[$key] = trim($data[$key]);
            }
        }

        $data['status'] = (int) ($data['status'] ?? 1);
        $data['is_sntc_recommended'] = (int) ($data['is_sntc_recommended'] ?? 0);
        $data['is_active_listing'] = (int) ($data['is_active_listing'] ?? 0);
        $data['user_id'] = $userId;

        // Default contact fields from analyser account when left blank.
        if (($data['contactPerson'] ?? '') === '') {
            $data['contactPerson'] = (string) $request->input('contact_person_name', '');
        }
        if (($data['contactMobile'] ?? '') === '') {
            $data['contactMobile'] = (string) $request->input('contact_mobile', '');
        }
        if (($data['registered_email'] ?? '') === '') {
            $data['registered_email'] = (string) $request->input('email', '');
        }

        WebBusinessDetails::updateOrCreate(['user_id' => $userId], $data);
    }

    private function sendCredentialsMail(string $email, int $otp, ?string $plainPassword = null): void
    {
        $user = User::where('email', $email)->first();
        $account = $user ? AnalyserAccount::where('user_id', $user->id)->first() : null;

        QueuedMail::send(
            'mail.analyserCredentials',
            [
                'userName' => $user ? $user->name : '',
                'email' => $email,
                'password' => $plainPassword,
                'otp' => $otp,
                'loginUrl' => config('analyser.login_url', 'abc'),
                'startDate' => $account && $account->start_date ? $account->start_date->format('d-m-Y') : '',
                'endDate' => $account && $account->end_date ? $account->end_date->format('d-m-Y') : '',
                'hasHistoricalAccess' => $account ? (bool) $account->has_historical_access : false,
                'hasTodayAccess' => $account ? (bool) $account->has_today_access : false,
            ],
            $email,
            'Your SNTC Analyser Account - Login Details',
            'info@sntcgroup.com',
            'SNTC Team - India',
            $user ? $user->name : ''
        );
    }
}
