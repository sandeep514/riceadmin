<?php

namespace App\Http\Controllers;

use App\Category;
use App\CategoryRoleMap;
use App\Role;
use App\Services\TradeWebNotificationService;
use App\Services\WebPortalNotificationDelivery;
use App\User;
use App\WebUserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

/**
 * Push Notification (new version): role + categories → Reverb/web + Firebase.
 */
class PushNotificationV2Controller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $roles = Role::query()->orderBy('role_name')->get(['id', 'role_name', 'type']);

        $sampleIds = DB::table('web_notifications')
            ->selectRaw('MAX(id) as id')
            ->where('audience_mode', 'push_notification_v2')
            ->groupBy('broadcast_group_id')
            ->pluck('id');

        $recipientCounts = WebUserNotification::query()
            ->select('broadcast_group_id', DB::raw('COUNT(*) as c'))
            ->where('audience_mode', 'push_notification_v2')
            ->groupBy('broadcast_group_id')
            ->pluck('c', 'broadcast_group_id');

        $history = $sampleIds->isEmpty()
            ? collect()
            : WebUserNotification::query()
                ->whereIn('id', $sampleIds)
                ->orderByDesc('created_at')
                ->limit(100)
                ->get();

        $oldRoleId = (int) old('role_id', 0);
        $categories = $oldRoleId > 0
            ? $this->categoriesForRole($oldRoleId)
            : collect();

        return view('push_notification_v2.index', compact('roles', 'categories', 'history', 'recipientCounts'));
    }

    public function categoriesByRole($roleId)
    {
        $roleId = (int) $roleId;
        if ($roleId <= 0) {
            return response()->json(['status' => true, 'data' => []]);
        }

        return response()->json([
            'status' => true,
            'data' => $this->categoriesForRole($roleId)->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'integer|exists:category,id',
            'title' => 'required|string|max:500',
            'message' => 'required|string',
        ]);

        $roleId = (int) $validated['role_id'];
        $categoryIds = array_values(array_unique(array_filter(array_map('intval', $validated['category_ids']))));
        $allowedIds = $this->categoriesForRole($roleId)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $categoryIds = array_values(array_intersect($categoryIds, $allowedIds));

        if ($categoryIds === []) {
            return back()
                ->withInput()
                ->with('error', 'Error|Select at least one category that belongs to the selected role.');
        }

        $title = trim($validated['title']);
        $message = trim($validated['message']);

        /** @var TradeWebNotificationService $tradeNotify */
        $tradeNotify = app(TradeWebNotificationService::class);
        /** @var WebPortalNotificationDelivery $delivery */
        $delivery = app(WebPortalNotificationDelivery::class);

        $webUserIds = $tradeNotify->eligibleWebUserIds($categoryIds, $roleId);
        $appFcmIds = $tradeNotify->eligibleAppUserIdsForFcm($categoryIds, $roleId);
        // App-only = has FCM token but was not already covered by the web/Reverb delivery path.
        $appOnlyFcmIds = array_values(array_diff($appFcmIds, $webUserIds));

        if ($webUserIds === [] && $appOnlyFcmIds === []) {
            return back()
                ->withInput()
                ->with('error', 'Error|No eligible web or app users found for this role and categories.');
        }

        if ($webUserIds !== []) {
            $delivery->deliverToUsers(
                $webUserIds,
                $title,
                $message,
                [
                    'role_id' => $roleId,
                    'audience_mode' => 'push_notification_v2',
                    'notify_date' => Carbon::now()->format('Y-m-d'),
                    'push_type' => 'admin',
                    'fill_role_from_user' => false,
                    'fill_category_from_business' => true,
                ]
            );
        }

        if ($appOnlyFcmIds !== []) {
            $delivery->queueFirebasePushForUserIds($appOnlyFcmIds, $title, $message, 'admin');
        }

        // Data Analyst / portal users are userType 2 — they count as "web", not "app-only".
        // FCM still queues for any of those recipients who have user_token set.
        $webWithFcm = $webUserIds === []
            ? 0
            : (int) User::query()
                ->whereIn('id', $webUserIds)
                ->whereNotNull('user_token')
                ->where('user_token', '!=', '')
                ->count();
        $fcmQueued = $webWithFcm + count($appOnlyFcmIds);

        $previewIds = array_slice($webUserIds, 0, 8);
        $flashBody = sprintf(
            'Success|Notification sent: %d web (Reverb), %d FCM queued (%d portal tokens + %d app-only). Socket user ids: %s%s',
            count($webUserIds),
            $fcmQueued,
            $webWithFcm,
            count($appOnlyFcmIds),
            $previewIds === [] ? '—' : implode(', ', $previewIds),
            count($webUserIds) > count($previewIds) ? '…' : ''
        );
        if ($fcmQueued === 0) {
            $flashBody .= ' No device tokens on matched users (socket still sent).';
        }

        Session::flash('success', $flashBody);

        return redirect()->route('push.notification.v2');
    }

    /**
     * Active categories mapped to the role in Role Category Map
     * (same source as Analyser Accounts).
     *
     * @return \Illuminate\Support\Collection<int, \App\Category>
     */
    private function categoriesForRole(int $roleId)
    {
        $mapQuery = CategoryRoleMap::query()->where('role', $roleId);
        if (Schema::hasColumn('category_role_map', 'status')) {
            $mapQuery->where('status', 1);
        }

        $categoryIds = $mapQuery
            ->pluck('category')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($categoryIds->isEmpty()) {
            return collect();
        }

        return Category::query()
            ->whereIn('id', $categoryIds)
            ->where(function ($q) {
                $q->where('status', 1)->orWhereNull('status');
            })
            ->orderByRaw('COALESCE(`order`, 999999)')
            ->orderBy('category')
            ->get(['id', 'category']);
    }
}
