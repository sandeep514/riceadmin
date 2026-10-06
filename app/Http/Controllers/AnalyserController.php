<?php

namespace App\Http\Controllers;

use App\AnalyserAccount;
use App\Http\Requests\AnalyserRequest;
use App\Role;
use App\Support\QueuedMail;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $query = AnalyserAccount::with('user')->orderBy('id', 'desc');

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
        return view('analyser_accounts.create');
    }

    public function store(AnalyserRequest $request)
    {
        $role = Role::firstOrCreate(['role_name' => 'analyser'], ['type' => 'analyser']);
        $otp = random_int(100000, 999999);

        DB::transaction(function () use ($request, $role, $otp) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => null,
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
        });

        if ($request->boolean('send_mail')) {
            $this->sendCredentialsMail($request->email, $otp);
        }

        Session::flash('success', 'Success|Analyser account created successfully!');
        return redirect()->route('analyser-accounts.index');
    }

    public function edit($id)
    {
        $account = AnalyserAccount::with('user')->find($id);
        if ($account == null) {
            Session::flash('error', 'Error|No record found!');
            return back();
        }

        return view('analyser_accounts.edit', ['model' => $account]);
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

        DB::transaction(function () use ($request, $account, $otp) {
            $account->user->update([
                'name' => $request->name,
                'email' => $request->email,
                'contact_person_name' => $request->contact_person_name,
                'mobile' => $request->contact_mobile,
            ] + ($otp ? ['otp' => $otp] : []));

            $account->update([
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'received_amount' => $request->received_amount,
                'contact_person_name' => $request->contact_person_name,
                'contact_mobile' => $request->contact_mobile,
                'has_historical_access' => $request->has_historical_access,
                'download_limit' => $request->download_limit,
                'has_today_access' => $request->has_today_access,
            ]);
        });

        if ($otp) {
            $this->sendCredentialsMail($request->email, $otp);
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
            User::where('id', $account->user_id)->delete();
            $account->delete();
        });

        Session::flash('success', 'Success|Analyser account deleted successfully!');
        return back();
    }

    private function sendCredentialsMail(string $email, int $otp): void
    {
        $user = User::where('email', $email)->first();
        $account = $user ? AnalyserAccount::where('user_id', $user->id)->first() : null;

        QueuedMail::send(
            'mail.analyserCredentials',
            [
                'userName' => $user ? $user->name : '',
                'email' => $email,
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
