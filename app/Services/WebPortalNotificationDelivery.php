<?php

namespace App\Services;

use App\Events\WebPortalNotificationEvent;
use App\Jobs\SendPushNotificationJob;
use App\Notification;
use App\User;
use App\WebUserNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Dual-channel portal notifications: web_notifications + Reverb/Pusher + Firebase FCM.
 *
 * Reverb: WebPortalNotificationEvent on private channel web-user.{id}
 * FCM: SendFcmForWebPortalNotification listener (same event) for app users with user_token
 */
class WebPortalNotificationDelivery
{
    public const DEFAULT_CHUNK_SIZE = 500;

    /**
     * Deliver to each user via web (DB + Pusher) and mobile FCM when user_token is set.
     *
     * @param  array<int>  $userIds
     * @param  array{
     *     role_id?: int|null,
     *     category_id?: int|null,
     *     audience_mode?: string,
     *     notify_date?: string,
     *     broadcast_group_id?: string|null,
     *     push_type?: string,
     *     fill_role_from_user?: bool,
     *     fill_category_from_business?: bool
     * }  $meta
     */
    public function deliverToUsers(
        array $userIds,
        string $title,
        string $message,
        array $meta = []
    ): void {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if ($userIds === []) {
            return;
        }

        $title = trim($title) !== '' ? trim($title) : 'Notification';
        $audienceMode = (string) ($meta['audience_mode'] ?? 'individual');
        $notifyDate = $meta['notify_date'] ?? Carbon::now()->format('Y-m-d');
        $groupId = ! empty($meta['broadcast_group_id'])
            ? (string) $meta['broadcast_group_id']
            : (string) Str::uuid();
        $pushType = (string) ($meta['push_type'] ?? 'portal');
        $fixedRoleId = array_key_exists('role_id', $meta) ? $meta['role_id'] : null;
        $fixedCategoryId = array_key_exists('category_id', $meta) ? $meta['category_id'] : null;
        $fillRoleFromUser = (bool) ($meta['fill_role_from_user'] ?? ($fixedRoleId === null));
        $fillCategoryFromBusiness = (bool) ($meta['fill_category_from_business'] ?? ($fixedCategoryId === null));

        $chunkSize = (int) (config('queue.trade_web_notification_chunk_size') ?: self::DEFAULT_CHUNK_SIZE);
        if ($chunkSize < 1) {
            $chunkSize = self::DEFAULT_CHUNK_SIZE;
        }

        foreach (array_chunk($userIds, $chunkSize) as $chunkIds) {
            $now = Carbon::now()->format('Y-m-d H:i:s');
            $rolesByUser = $fillRoleFromUser
                ? User::query()->whereIn('id', $chunkIds)->pluck('role', 'id')
                : collect();
            $categoriesByUser = $fillCategoryFromBusiness
                ? DB::table('web_business_details')
                    ->whereIn('user_id', $chunkIds)
                    ->pluck('selected_category', 'user_id')
                : collect();

            $rows = [];
            foreach ($chunkIds as $userId) {
                $roleId = $fixedRoleId;
                if ($roleId === null && $fillRoleFromUser) {
                    $roleId = isset($rolesByUser[$userId]) ? (int) $rolesByUser[$userId] : null;
                }

                $categoryId = $fixedCategoryId;
                if ($categoryId === null && $fillCategoryFromBusiness) {
                    $categoryId = isset($categoriesByUser[$userId]) ? (int) $categoriesByUser[$userId] : null;
                }

                $rows[] = [
                    'user_id' => $userId,
                    'notify_date' => $notifyDate,
                    'title' => $title,
                    'message' => $message,
                    'role_id' => $roleId !== null && (int) $roleId > 0 ? (int) $roleId : null,
                    'category_id' => $categoryId !== null && (int) $categoryId > 0 ? (int) $categoryId : null,
                    'audience_mode' => $audienceMode,
                    'broadcast_group_id' => $groupId,
                    'read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            WebUserNotification::insert($rows);

            // Do not filter by created_at: TIMESTAMP columns / TZ can make exact
            // equality miss rows and skip every Pusher publish.
            $created = WebUserNotification::query()
                ->where('broadcast_group_id', $groupId)
                ->whereIn('user_id', $chunkIds)
                ->get();

            if ($created->isEmpty()) {
                Log::error('Portal notifications inserted but none loaded for broadcast.', [
                    'group_id' => $groupId,
                    'user_ids' => $chunkIds,
                ]);
            }

            foreach ($created as $notification) {
                try {
                    broadcast(new WebPortalNotificationEvent($notification));
                } catch (\Throwable $e) {
                    Log::error('Portal notification broadcast failed for user.', [
                        'group_id' => $groupId,
                        'user_id' => $notification->user_id,
                        'notification_id' => $notification->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // History for portal/Pusher recipients (including users without an FCM token).
            $this->persistNotificationRows($chunkIds, $title, $message, $pushType, $now);

            // FCM is queued by SendFcmForWebPortalNotification when each Reverb event fires.
        }
    }

    /**
     * @param  array<int>  $userIds
     */
    public function queueFirebasePushForUserIds(
        array $userIds,
        string $title,
        string $message,
        string $pushType = 'portal',
        ?int $chunkSize = null,
        bool $persistToNotificationTable = true
    ): void {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if ($userIds === []) {
            return;
        }

        $tokenUsers = User::query()
            ->whereIn('id', $userIds)
            ->whereNotNull('user_token')
            ->where('user_token', '!=', '')
            ->select('id', 'user_token')
            ->get()
            ->map(fn ($u) => ['id' => (int) $u->id, 'user_token' => (string) $u->user_token])
            ->values()
            ->all();

        if ($tokenUsers === []) {
            return;
        }

        $chunkSize = $chunkSize ?? (int) (config('queue.trade_web_notification_chunk_size') ?: self::DEFAULT_CHUNK_SIZE);
        if ($chunkSize < 1) {
            $chunkSize = self::DEFAULT_CHUNK_SIZE;
        }

        foreach (array_chunk($tokenUsers, $chunkSize) as $chunk) {
            // Sync so trade create/update FCM is not left sitting in the database queue.
            SendPushNotificationJob::dispatchSync(
                $title,
                $message,
                $chunk,
                $pushType,
                $persistToNotificationTable
            );
        }
    }

    /**
     * @param  array<int>  $userIds
     */
    private function persistNotificationRows(
        array $userIds,
        string $title,
        string $message,
        string $pushType,
        string $now
    ): void {
        $rows = [];
        foreach ($userIds as $userId) {
            $rows[] = [
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'userAppType' => $pushType,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            Notification::insert($rows);
        }
    }
}
