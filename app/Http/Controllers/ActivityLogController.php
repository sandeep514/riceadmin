<?php

namespace App\Http\Controllers;

use App\Category;
use App\PlanPageVisit;
use App\Role;
use App\User;
use App\Vendorcategory;
use App\WebUserSubscriptionModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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

    /** @var array<string, string> */
    private const SOURCE_OPTIONS = [
        'all' => 'Web + App',
        'web' => 'Web only',
        'app' => 'App only',
    ];

    /**
     * Users who recently used the website or mobile app (API activity), not login-only.
     */
    public function lastLoginUsers(Request $request)
    {
        $period = $this->normalizePeriod($request->input('period', '15d'));
        $source = $this->normalizeSource($request->input('source', 'all'));
        $from = $this->periodStart($period);

        $hasActive = Schema::hasColumn('users', 'last_active_at');
        $hasLogin = Schema::hasColumn('users', 'last_login_at');
        $activityColumn = $hasActive ? 'last_active_at' : ($hasLogin ? 'last_login_at' : null);

        $users = collect();
        if ($activityColumn) {
            $query = User::query()
                ->whereNotNull($activityColumn)
                ->where($activityColumn, '>=', $from);

            $this->applyClientUserScope($query, $source);

            $users = $query
                ->with($this->userListRelations())
                ->orderByDesc($activityColumn)
                ->limit(2000)
                ->get();
        }

        $periods = self::PERIOD_OPTIONS;
        $sources = self::SOURCE_OPTIONS;

        return view('activity_logs.last_login', compact(
            'users',
            'period',
            'periods',
            'source',
            'sources',
            'from',
            'activityColumn'
        ));
    }

    /**
     * Users who opened the plan page but never bought or activated trial
     * (web subscriptions + app planId / transaction / trial).
     */
    public function planPageUsers(Request $request)
    {
        $period = $this->normalizePeriod($request->input('period', '15d'));
        $source = $this->normalizeSource($request->input('source', 'all'));
        $from = $this->periodStart($period);

        $users = collect();
        if (Schema::hasTable('plan_page_visits')) {
            $latestVisit = PlanPageVisit::query()
                ->select(
                    'user_id',
                    DB::raw('MAX(visited_at) as last_visited_at'),
                    DB::raw('COUNT(*) as visit_count')
                )
                ->where('visited_at', '>=', $from)
                ->groupBy('user_id');

            $subscribedWebIds = WebUserSubscriptionModel::query()
                ->select('user_id')
                ->distinct();

            $query = User::query()
                ->joinSub($latestVisit, 'ppv', function ($join) {
                    $join->on('users.id', '=', 'ppv.user_id');
                })
                // No web subscription AND no app plan/trial markers.
                ->whereNotIn('users.id', $subscribedWebIds)
                ->where(function ($q) {
                    $q->whereNull('users.planId')
                        ->orWhere('users.planId', '')
                        ->orWhere('users.planId', 0);
                })
                ->where(function ($q) {
                    $q->whereNull('users.transaction_id')
                        ->orWhere('users.transaction_id', '');
                })
                ->whereNull('users.expired_on');

            $this->applyClientUserScope($query, $source);

            $users = $query
                ->with($this->userListRelations())
                ->orderByDesc('ppv.last_visited_at')
                ->select('users.*', 'ppv.last_visited_at', 'ppv.visit_count')
                ->limit(2000)
                ->get();
        }

        $periods = self::PERIOD_OPTIONS;
        $sources = self::SOURCE_OPTIONS;

        return view('activity_logs.plan_page', compact(
            'users',
            'period',
            'periods',
            'source',
            'sources',
            'from'
        ));
    }

    public function newUsers(Request $request)
    {
        $from = trim((string) $request->input('from'));
        $to = trim((string) $request->input('to'));
        $roleId = $request->input('role');
        $categoryId = $request->input('category');
        $source = $this->normalizeSource($request->input('source', 'all'));

        if ($from === '' && $to === '') {
            $from = Carbon::now('Asia/Kolkata')->subDays(15)->format('Y-m-d');
            $to = Carbon::now('Asia/Kolkata')->format('Y-m-d');
        }

        $query = User::query();
        $this->applyClientUserScope($query, $source);
        $query->with($this->userListRelations())->orderByDesc('id');

        if ($from !== '') {
            $query->where('created_at', '>=', Carbon::parse($from, 'Asia/Kolkata')->startOfDay());
        }
        if ($to !== '') {
            $query->where('created_at', '<=', Carbon::parse($to, 'Asia/Kolkata')->endOfDay());
        }
        if ($roleId !== null && $roleId !== '') {
            $rid = (int) $roleId;
            $query->where(function ($q) use ($rid) {
                $q->where('role', $rid)->orWhere('usd_role', $rid);
            });
        }
        if ($categoryId !== null && $categoryId !== '') {
            $cid = (int) $categoryId;
            $query->where(function ($q) use ($cid) {
                $q->whereHas('getWebBusinessDetails', function ($bq) use ($cid) {
                    $bq->where('selected_category', $cid);
                })->orWhere('bagCategory', $cid);
            });
        }

        $users = $query->limit(2000)->get();

        $roles = Role::query()
            ->where(function ($q) {
                $q->whereRaw('LOWER(TRIM(type)) = ?', ['web'])
                    ->orWhereRaw('LOWER(TRIM(type)) = ?', ['app'])
                    ->orWhereNull('type')
                    ->orWhere('type', '');
            })
            ->orderBy('role_name')
            ->get(['id', 'role_name', 'type']);

        $categories = Category::query()
            ->where(function ($q) {
                $q->where('status', 1)->orWhereNull('status');
            })
            ->orderBy('category')
            ->get(['id', 'category']);

        // App bag categories (vendorcategory) — merge into filter list by id/name if distinct.
        $bagCategories = Vendorcategory::query()
            ->orderBy('category')
            ->get(['id', 'category']);

        $sources = self::SOURCE_OPTIONS;

        return view('activity_logs.new_users', compact(
            'users',
            'from',
            'to',
            'roleId',
            'categoryId',
            'roles',
            'categories',
            'bagCategories',
            'source',
            'sources'
        ));
    }

    /**
     * Portal / app clients only (exclude admin panel accounts).
     */
    private function applyClientUserScope(Builder $query, string $source): void
    {
        $query->whereIn('userType', [1, 2]);

        if ($source === 'web') {
            $query->where('userType', 2);
        } elseif ($source === 'app') {
            $query->where('userType', 1);
        }
    }

    private function userListRelations(): array
    {
        return [
            'role_rel:id,role_name,type',
            'role_rel_usd:id,role_name,type',
            'bagVendor:id,category',
            'getWebBusinessDetails' => function ($q) {
                $q->with(['getCategoryDetails:id,category']);
            },
        ];
    }

    private function normalizePeriod(?string $period): string
    {
        $period = strtolower(trim((string) $period));

        return array_key_exists($period, self::PERIOD_OPTIONS) ? $period : '15d';
    }

    private function normalizeSource(?string $source): string
    {
        $source = strtolower(trim((string) $source));

        return array_key_exists($source, self::SOURCE_OPTIONS) ? $source : 'all';
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
