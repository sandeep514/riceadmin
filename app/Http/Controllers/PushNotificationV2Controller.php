<?php

namespace App\Http\Controllers;

use App\Category;
use App\Role;
use App\Services\TradeWebNotificationService;
use App\Services\WebPortalNotificationDelivery;
use App\WebUserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $categories = Category::query()
            ->where('status', 1)
            ->orderBy('category')
            ->get(['id', 'category']);

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

        return view('push_notification_v2.index', compact('roles', 'categories', 'history', 'recipientCounts'));
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
        $title = trim($validated['title']);
        $message = trim($validated['message']);

        /** @var TradeWebNotificationService $tradeNotify */
        $tradeNotify = app(TradeWebNotificationService::class);
        /** @var WebPortalNotificationDelivery $delivery */
        $delivery = app(WebPortalNotificationDelivery::class);

        $webUserIds = $tradeNotify->eligibleWebUserIds($categoryIds, $roleId);
        $appFcmIds = $tradeNotify->eligibleAppUserIdsForFcm($categoryIds, $roleId);
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

        Session::flash(
            'success',
            sprintf(
                'Success|Notification sent: %d web (Reverb/Firebase), %d app FCM.',
                count($webUserIds),
                count($appOnlyFcmIds)
            )
        );

        return redirect()->route('push.notification.v2');
    }
}
