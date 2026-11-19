<?php

namespace App\Http\Controllers;

use App\Category;
use App\PlanPageVisit;
use App\Role;
use App\User;
use App\WebUserSubscriptionModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ActivityLogController extends Controller
{
    /** @var array<string, string> */
    private const PERIOD_OPTIONS = [
        '15d' => 'Last 15 days',
        '1m' => 'Last 1 month',
        '3m' => 'Last 3 months',
        '6m' => 'Last 6 months',
        '1y' => 'Last 1 year',
    ];

    public function lastLoginUsers(Request $request)
    {
        $period = $this->normalizePeriod($request->input('period', '15d'));
        $from = $this->periodStart($period);

        $users = collect();
        if (Schema::hasColumn('users', 'last_login_at')) {
            $users = User::query()
                ->whereNotNull('last_login_at')
                ->where('last_login_at', '>=', $from)
                ->with([
                    'role_rel:id,role_name,type',
                    'getWebBusinessDetails' => function ($q) {
                        $q->with(['getCategoryDetails:id,category']);
                    },
                ])
                ->orderByDesc('last_login_at')
                ->limit(2000)
                ->get();
        }

        $periods = self::PERIOD_OPTIONS;

        return view('activity_logs.last_login', compact('users', 'period', 'periods', 'from'));
    }

    /**
     * Users who opened the plan page (API) but never bought or activated trial.
     */
    public function planPageUsers(Request $request)
    {
        $period = $this->normalizePeriod($request->input('period', '15d'));
        $from = $this->periodStart($period);

        $users = collect();
        if (Schema::hasTable('plan_page_visits')) {
            $latestVisit = PlanPageVisit::query()
                ->select('user_id', DB::raw('MAX(visited_at) as last_visited_at'), DB::raw('COUNT(*) as visit_count'))
                ->where('visited_at', '>=', $from)
                ->groupBy('user_id');

            $subscribedIds = WebUserSubscriptionModel::query()
                ->select('user_id')
                ->distinct();

            $users = User::query()
                ->joinSub($latestVisit, 'ppv', function ($join) {
                    $join->on('users.id', '=', 'ppv.user_id');
                })
                ->whereNotIn('users.id', $subscribedIds)
                ->with([
                    'role_rel:id,role_name,type',
                    'getWebBusinessDetails' => function ($q) {
                        $q->with(['getCategoryDetails:id,category']);
                    },
                ])
                ->orderByDesc('ppv.last_visited_at')
                ->select('users.*', 'ppv.last_visited_at', 'ppv.visit_count')
                ->limit(2000)
                ->get();
        }

        $periods = self::PERIOD_OPTIONS;

        return view('activity_logs.plan_page', compact('users', 'period', 'periods', 'from'));
    }

    public function newUsers(Request $request)
    {
        $from = trim((string) $request->input('from'));
        $to = trim((string) $request->input('to'));
        $roleId = $request->input('role');
        $categoryId = $request->input('category');

        if ($from === '' && $to === '') {
            $from = Carbon::now('Asia/Kolkata')->subDays(15)->format('Y-m-d');
            $to = Carbon::now('Asia/Kolkata')->format('Y-m-d');
        }

        $query = User::query()
            ->where(function ($q) {
                $q->where('user_from', 'web')
                    ->orWhere('userType', 2);
            })
            ->with([
                'role_rel:id,role_name,type',
                'getWebBusinessDetails' => function ($q) {
                    $q->with(['getCategoryDetails:id,category']);
                },
            ])
            ->orderByDesc('id');

        if ($from !== '') {
            $query->where('created_at', '>=', Carbon::parse($from, 'Asia/Kolkata')->startOfDay());
        }
        if ($to !== '') {
            $query->where('created_at', '<=', Carbon::parse($to, 'Asia/Kolkata')->endOfDay());
        }
        if ($roleId !== null && $roleId !== '') {
            $query->where('role', (int) $roleId);
        }
        if ($categoryId !== null && $categoryId !== '') {
            $cid = (int) $categoryId;
            $query->whereHas('getWebBusinessDetails', function ($q) use ($cid) {
                $q->where('selected_category', $cid);
            });
        }

        $users = $query->limit(2000)->get();

        $roles = Role::query()
            ->whereRaw('LOWER(TRIM(type)) = ?', ['web'])
            ->orderBy('role_name')
            ->get(['id', 'role_name']);

        $categories = Category::query()
            ->where(function ($q) {
                $q->where('status', 1)->orWhereNull('status');
            })
            ->orderBy('category')
            ->get(['id', 'category']);

        return view('activity_logs.new_users', compact(
            'users',
            'from',
            'to',
            'roleId',
            'categoryId',
            'roles',
            'categories'
        ));
    }

    private function normalizePeriod(?string $period): string
    {
        $period = strtolower(trim((string) $period));

        return array_key_exists($period, self::PERIOD_OPTIONS) ? $period : '15d';
    }

    private function periodStart(string $period): Carbon
    {
        $now = Carbon::now('Asia/Kolkata');

        return match ($period) {
            '1m' => $now->copy()->subMonth()->startOfDay(),
            '3m' => $now->copy()->subMonths(3)->startOfDay(),
            '6m' => $now->copy()->subMonths(6)->startOfDay(),
            '1y' => $now->copy()->subYear()->startOfDay(),
            default => $now->copy()->subDays(15)->startOfDay(),
        };
    }
}
