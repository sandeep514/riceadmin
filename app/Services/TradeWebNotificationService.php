<?php

namespace App\Services;

use App\CategoryRoleMap;
use App\Events\WebPortalNotificationEvent;
use App\Jobs\SendPushNotificationJob;
use App\Jobs\SendTradeInterestNotificationsJob;
use App\Jobs\SendTradeWebNotificationsJob;
use App\TradeQueriesINR;
use App\User;
use App\WebUserNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TradeWebNotificationService
{
    public const DEFAULT_CHUNK_SIZE = 500;
    /** Default message for users whose saved interests match the new trade. */
    public const DEFAULT_TRADE_INTEREST_NOTIFY_MESSAGE = 'SNTC send you special notification on a new trade {trade_no}, {quality}, {rice_form}, {grade}.';

    /** Default notification body; placeholders are replaced from the saved trade. */
    public const DEFAULT_TRADE_NOTIFY_MESSAGE = 'A new trade is added (Trade #{trade_no}).

Trade Type: {trade_type}
Farming Type: {farming_type}
Quality: {quality}
Rice Form: {rice_form}
Grade: {grade}
Quantity: {quantity}';

    /**
     * Notify users about a trade (web Reverb + mobile FCM when token exists).
     *
     * @param  array<int>  $categoryIds
     * @param  array<int>|null  $selectedUserIds
     */
    public function send(
        TradeQueriesINR $trade,
        array $categoryIds,
        bool $send,
        string $audienceMode,
        ?array $selectedUserIds,
        string $title,
        string $messageTemplate,
        ?int $roleId = null
    ): void {
        $this->processTradeNotification(
            $trade,
            $categoryIds,
            $send,
            $audienceMode,
            $selectedUserIds,
            $title,
            $messageTemplate,
            $roleId
        );
    }

    /**
     * @param  array<int>  $userIds
     */
    public function sendInterestMatch(
        TradeQueriesINR $trade,
        array $userIds,
        string $title,
        string $messageTemplate
    ): void {
        $this->processInterestNotification($trade, $userIds, $title, $messageTemplate);
    }

    public function queueTradeNotification(
        int $tradeId,
        array $categoryIds,
        bool $send,
        string $audienceMode,
        ?array $selectedUserIds,
        string $title,
        string $messageTemplate,
        ?int $roleId = null
    ): void {
        SendTradeWebNotificationsJob::dispatch(
            $tradeId,
            $categoryIds,
            $send,
            $audienceMode,
            $selectedUserIds,
            $title,
            $messageTemplate,
            $roleId
        )->onQueue((string) config('queue.trade_notification_queue', 'default'));
    }

    public function queueInterestNotification(
        int $tradeId,
        array $userIds,
        string $title,
        string $messageTemplate
    ): void {
        SendTradeInterestNotificationsJob::dispatch(
            $tradeId,
            $userIds,
            $title,
            $messageTemplate
        )->onQueue((string) config('queue.trade_notification_queue', 'default'));
    }

    public function processTradeNotification(
        TradeQueriesINR $trade,
        array $categoryIds,
        bool $send,
        string $audienceMode,
        ?array $selectedUserIds,
        string $title,
        string $messageTemplate,
        ?int $roleId = null
    ): void {
        if (! $send) {
            return;
        }

        $categoryIds = array_values(array_unique(array_filter(array_map('intval', $categoryIds))));
        if ($categoryIds === []) {
            return;
        }

        $trade->loadMissing(['RiceNameData', 'RiceFormMilestone3', 'riceGrade.getWandType']);
        $message = $this->applyTradeMessagePlaceholders($trade, $messageTemplate);
        $title = trim($title) !== '' ? trim($title) : 'New Trade alert';

        $targetIds = $this->resolveTradeTargetUserIds(
            $categoryIds,
            $audienceMode,
            $selectedUserIds,
            $roleId
        );

        if ($targetIds === []) {
            return;
        }

        $this->insertAndBroadcastInChunks(
            $targetIds,
            $title,
            $message,
            $audienceMode === 'selected_users' ? 'selected_users' : 'all_category',
            $roleId
        );

        // App users matching role (+ category-linked roles) get FCM only.
        $appFcmIds = $this->eligibleAppUserIdsForFcm($categoryIds, $roleId);
        if ($audienceMode === 'selected_users') {
            $picked = array_values(array_unique(array_filter(array_map('intval', $selectedUserIds ?? []))));
            $appFcmIds = array_values(array_intersect($appFcmIds, $picked));
        }
        // Avoid double FCM for users already covered as web targets.
        $appFcmIds = array_values(array_diff($appFcmIds, $targetIds));
        if ($appFcmIds !== []) {
            $this->queueFirebasePushForUserIds($appFcmIds, $title, $message);
        }
    }

    public function processInterestNotification(
        TradeQueriesINR $trade,
        array $userIds,
        string $title,
        string $messageTemplate
    ): void {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if ($userIds === []) {
            return;
        }

        $trade->loadMissing(['RiceNameData', 'RiceFormMilestone3', 'riceGrade.getWandType']);
        $message = $this->applyTradeMessagePlaceholders($trade, $messageTemplate);
        $title = trim($title) !== '' ? trim($title) : 'Special trade alert';

        $webUserIds = User::query()
            ->where('user_from', 'web')
            ->whereIn('id', $userIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if ($webUserIds === []) {
            return;
        }

        $this->insertAndBroadcastInChunks($webUserIds, $title, $message, 'trade_interest', null);
    }

    /**
     * Web portal users matching selected categories (and optional role).
     *
     * @return array<int>
     */
    public function eligibleWebUserIds(array $categoryIds, ?int $roleId = null): array
    {
        $categoryIds = array_values(array_unique(array_filter(array_map('intval', $categoryIds))));
        if ($categoryIds === []) {
            return [];
        }

        $query = User::query()
            ->where('users.userType', 2)
            ->where('users.user_from', 'web')
            ->join('web_business_details as wbd', 'wbd.user_id', '=', 'users.id')
            ->whereIn('wbd.selected_category', $categoryIds);

        if ($roleId !== null && $roleId > 0) {
            $query->where('users.role', $roleId);
        }

        return $query
            ->distinct()
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * App/mobile users with FCM tokens for roles linked to the trade categories (or explicit role).
     *
     * @return array<int>
     */
    public function eligibleAppUserIdsForFcm(array $categoryIds, ?int $roleId = null): array
    {
        $roleIds = [];
        if ($roleId !== null && $roleId > 0) {
            $roleIds[] = $roleId;
        } else {
            $categoryIds = array_values(array_unique(array_filter(array_map('intval', $categoryIds))));
            if ($categoryIds === []) {
                return [];
            }
            $roleIds = CategoryRoleMap::query()
                ->whereIn('category', $categoryIds)
                ->where('status', 1)
                ->pluck('role')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();
        }

        if ($roleIds === []) {
            return [];
        }

        return User::query()
            ->where(function ($q) {
                $q->where('user_from', 'app')
                    ->orWhere('userType', 1);
            })
            ->whereIn('role', $roleIds)
            ->whereNotNull('user_token')
            ->where('user_token', '!=', '')
            ->where('id', '!=', 301)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @param array<int> $categoryIds
     * @param array<int>|null $selectedUserIds
     * @return array<int>
     */
    public function resolveTradeTargetUserIds(
        array $categoryIds,
        string $audienceMode,
        ?array $selectedUserIds = null,
        ?int $roleId = null
    ): array {
        $eligibleIds = $this->eligibleWebUserIds($categoryIds, $roleId);
        if ($eligibleIds === []) {
            return [];
        }

        if ($audienceMode === 'selected_users') {
            $picked = array_values(array_unique(array_filter(array_map('intval', $selectedUserIds ?? []))));

            return array_values(array_intersect($picked, $eligibleIds));
        }

        return $eligibleIds;
    }

    private function insertAndBroadcastInChunks(
        array $targetIds,
        string $title,
        string $message,
        string $audienceMode,
        ?int $roleId = null
    ): void {
        $groupId = (string) Str::uuid();
        $notifyDate = Carbon::now()->format('Y-m-d');
        $chunkSize = (int) (config('queue.trade_web_notification_chunk_size') ?: self::DEFAULT_CHUNK_SIZE);
        if ($chunkSize < 1) {
            $chunkSize = self::DEFAULT_CHUNK_SIZE;
        }

        foreach (array_chunk($targetIds, $chunkSize) as $chunkIds) {
            $now = Carbon::now()->format('Y-m-d H:i:s');
            $rolesByUser = User::query()->whereIn('id', $chunkIds)->pluck('role', 'id');
            $categoriesByUser = DB::table('web_business_details')
                ->whereIn('user_id', $chunkIds)
                ->pluck('selected_category', 'user_id');

            $rows = [];
            foreach ($chunkIds as $userId) {
                $rows[] = [
                    'user_id' => $userId,
                    'notify_date' => $notifyDate,
                    'title' => $title,
                    'message' => $message,
                    'role_id' => $roleId !== null && $roleId > 0
                        ? $roleId
                        : (isset($rolesByUser[$userId]) ? (int) $rolesByUser[$userId] : null),
                    'category_id' => isset($categoriesByUser[$userId]) ? (int) $categoriesByUser[$userId] : null,
                    'audience_mode' => $audienceMode,
                    'broadcast_group_id' => $groupId,
                    'read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            WebUserNotification::insert($rows);

            $created = WebUserNotification::query()
                ->where('broadcast_group_id', $groupId)
                ->whereIn('user_id', $chunkIds)
                ->where('created_at', $now)
                ->get();

            foreach ($created as $notification) {
                try {
                    // Web (Pusher / Reverb private channel)
                    broadcast(new WebPortalNotificationEvent($notification));
                } catch (\Throwable $e) {
                    Log::warning('Trade web notification broadcast failed for user.', [
                        'group_id' => $groupId,
                        'user_id' => $notification->user_id,
                        'notification_id' => $notification->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Mobile (Firebase) for the same accounts when they have an FCM token
            $this->queueFirebasePushForUserIds($chunkIds, $title, $message);
        }
    }

    /**
     * @param  array<int>  $userIds
     */
    private function queueFirebasePushForUserIds(array $userIds, string $title, string $message): void
    {
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

        $chunkSize = (int) (config('queue.trade_web_notification_chunk_size') ?: self::DEFAULT_CHUNK_SIZE);
        if ($chunkSize < 1) {
            $chunkSize = self::DEFAULT_CHUNK_SIZE;
        }

        foreach (array_chunk($tokenUsers, $chunkSize) as $chunk) {
            SendPushNotificationJob::dispatch(
                $title,
                $message,
                $chunk,
                'trade'
            )->onQueue((string) config('queue.trade_notification_queue', 'default'));
        }
    }

    private function resolveTradeNo(TradeQueriesINR $trade): string
    {
        $qid = trim((string) ($trade->queryId ?? ''));
        if ($qid !== '' && $qid !== '0') {
            return $qid;
        }

        return (string) $trade->id;
    }

    /**
     * Replace placeholders with trade field labels. Supported tokens include
     * {trade_no}, {trade_type}, {farming_type}, {quality}, {rice_form}, {grade}, {quantity}.
     */
    public function applyTradeMessagePlaceholders(TradeQueriesINR $trade, string $messageTemplate): string
    {
        $trade->loadMissing(['RiceNameData', 'RiceFormMilestone3', 'riceGrade.getWandType']);

        $tradeNo = $this->resolveTradeNo($trade);

        $tradeTypeKey = (int) ($trade->tradeType ?? 0);
        $tradeTypeLabel = TradeQueriesINR::$tradeType[$tradeTypeKey] ?? (string) ($trade->tradeType ?? '');

        $farmingKey = (int) ($trade->farmingType ?? 0);
        $farmingLabel = TradeQueriesINR::$farmingTypeWeb[$farmingKey]
            ?? TradeQueriesINR::$farmingType[$farmingKey]
            ?? (string) ($trade->farmingType ?? '');

        $qualityLabel = optional($trade->RiceNameData)->name ?? '';

        $riceFormLabel = optional($trade->RiceFormMilestone3)->name ?? '';

        $gradeLabel = '';
        if ($trade->riceGrade) {
            $w = $trade->riceGrade;
            $gradeLabel = trim((string) (($w->getWandType->type ?? '') . ' ' . ($w->value ?? '')));
        }

        $quantityLabel = trim((string) ($trade->quantity ?? ''));
        if ($quantityLabel === '' && $trade->quantity !== null) {
            $quantityLabel = (string) $trade->quantity;
        }

        $map = [
            '{trade_no}' => $tradeNo,
            '{tradeNo}' => $tradeNo,
            '{TRADE_NO}' => $tradeNo,
            '{trade_type}' => $tradeTypeLabel,
            '{tradeType}' => $tradeTypeLabel,
            '{farming_type}' => $farmingLabel,
            '{farmingType}' => $farmingLabel,
            '{quality}' => $qualityLabel,
            '{rice_form}' => $riceFormLabel,
            '{riceForm}' => $riceFormLabel,
            '{grade}' => $gradeLabel,
            '{quantity}' => $quantityLabel,
        ];

        return str_replace(array_keys($map), array_values($map), $messageTemplate);
    }
}
