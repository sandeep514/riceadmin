<?php

namespace App\Http\Controllers;

use App\Category;
use App\Role;
use App\Services\WebPortalNotificationDelivery;
use App\User;
use App\WebPlanModel;
use App\WebUserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class NotifyWebUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $roles = Role::where('type', 'web')->orderBy('role_name')->get();

        $sampleIds = DB::table('web_notifications')
            ->selectRaw('MAX(id) as id')
            ->groupBy('broadcast_group_id')
            ->pluck('id');

        $recipientCounts = WebUserNotification::query()
            ->select('broadcast_group_id', DB::raw('COUNT(*) as c'))
            ->groupBy('broadcast_group_id')
            ->pluck('c', 'broadcast_group_id');

        if ($sampleIds->isEmpty()) {
            $history = collect();
        } else {
            $history = WebUserNotification::query()
                ->whereIn('id', $sampleIds)
                ->orderByDesc('created_at')
                ->limit(100)
                ->get();
        }

        return view('notify_web_user.index', compact('roles', 'history', 'recipientCounts'));
    }

    public function categoriesByRole($roleId)
    {
        $roleId = (int) $roleId;
        $categoryIds = WebPlanModel::query()
            ->where('role_id', $roleId)
            ->where('status', 1)
            ->whereNotNull('category_id')
            ->distinct()
            ->pluck('category_id');

        $categories = Category::query()
            ->whereIn('id', $categoryIds)
            ->orderBy('category')
            ->get(['id', 'category']);

        return response()->json(['status' => true, 'data' => $categories]);
    }

    public function usersByRoleCategory(Request $request)
    {
        $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
            'category_id' => 'required|integer|exists:category,id',
        ]);

        $users = $this->queryWebUsersForRoleCategory(
            (int) $request->role_id,
            (int) $request->category_id
        )->orderBy('id', 'desc')->get(['id', 'name', 'mobile', 'email']);

        return response()->json(['status' => true, 'data' => $users]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'notify_date' => 'required|date',
            'title' => 'required|string|max:500',
            'message' => 'required|string',
            'role_id' => 'required|integer|exists:roles,id',
            'category_id' => 'required|integer|exists:category,id',
            'audience_mode' => 'required|in:all_category,selected_users',
            'user_ids' => 'required_if:audience_mode,selected_users|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $roleId = (int) $validated['role_id'];
        $categoryId = (int) $validated['category_id'];
        $audienceMode = $validated['audience_mode'];

        if ($audienceMode === 'selected_users') {
            $userIds = array_values(array_unique(array_map('intval', $validated['user_ids'] ?? [])));
            if (count($userIds) === 0) {
                return back()->withInput()->with('error', 'Error|Select at least one user.');
            }
            $validIds = $this->queryWebUsersForRoleCategory($roleId, $categoryId)
                ->whereIn('users.id', $userIds)
                ->pluck('users.id')
                ->all();
            if (count($validIds) !== count($userIds)) {
                return back()->withInput()->with('error', 'Error|One or more users do not belong to the selected role and category.');
            }
            $userIds = $validIds;
        } else {
            $userIds = $this->queryWebUsersForRoleCategory($roleId, $categoryId)->pluck('users.id')->all();
        }

        if (count($userIds) === 0) {
            return back()->withInput()->with('error', 'Error|No web users found for this role and category.');
        }

        app(WebPortalNotificationDelivery::class)->deliverToUsers(
            $userIds,
            $validated['title'],
            $validated['message'],
            [
                'role_id' => $roleId,
                'category_id' => $categoryId,
                'audience_mode' => $audienceMode,
                'notify_date' => Carbon::parse($validated['notify_date'])->format('Y-m-d'),
                'push_type' => 'admin',
                'fill_role_from_user' => false,
                'fill_category_from_business' => false,
            ]
        );

        Session::flash('success', 'Success|Notification sent to ' . count($userIds) . ' user(s).');

        return redirect()->route('notify.web.user');
    }

    private function queryWebUsersForRoleCategory(int $roleId, int $categoryId)
    {
        return User::query()
            ->where('users.userType', 2)
            ->where('users.user_from', 'web')
            ->where('users.role', $roleId)
            ->join('web_business_details as wbd', 'wbd.user_id', '=', 'users.id')
            ->where('wbd.selected_category', $categoryId)
            ->select('users.id', 'users.name', 'users.mobile', 'users.email')
            ->distinct();
    }
}
