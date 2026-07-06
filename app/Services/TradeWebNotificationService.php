<?php

namespace App\Services;

use App\Events\WebPortalNotificationEvent;
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
     * Notify web portal users about a trade. Inserts web_notifications rows and broadcasts per user.
     *
     * @param  array<int>  $categoryIds  Web category ids (from trade_category_map / form)
     * @param  array<int>|null  $selectedUserIds  When audience is selected_users
     */
    public function send(
        TradeQueriesINR $trade,
        array $categoryIds,
        bool $send,
        string $audienceMode,
        ?array $selectedUserIds,
        string $title,
        string $messageTemplate
    ): void {
        $this->processTradeNotification(
            $trade,
            $categoryIds,
            $send,
            $audienceMode,
            $selectedUserIds,
            $title,
            $messageTemplate
        );
    }

    /**
     * Notify web users who were selected because their saved interests match the trade.
     *
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
        string $messageTemplate
    ): void {
        SendTradeWebNotificationsJob::dispatch(
            $tradeId,
            $categoryIds,
            $send,
            $audienceMode,
            $selectedUserIds,
            $title,
            $messageTemplate
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
        string $messageTemplate
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

        $targetIds = $this->resolveTradeTargetUserIds($categoryIds, $audienceMode, $selectedUserIds);

        if ($targetIds === []) {
            return;
        }

        $this->insertAndBroadcastInChunks(
            $targetIds,
            $title,
            $message,
            $audienceMode === 'selected_users' ? 'selected_users' : 'all_category'
        );
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

        $this->insertAndBroadcastInChunks($webUserIds, $title, $message, 'trade_interest');
    }

    /**
     * @return array<int>
     */
    public function eligibleWebUserIds(array $categoryIds): array
    {
        $categoryIds = array_values(array_unique(array_filter(array_map('intval', $categoryIds))));
        if ($categoryIds === []) {
            return [];
        }

        return User::query()
            ->where('users.userType', 2)
            ->where('users.user_from', 'web')
            ->join('web_business_details as wbd', 'wbd.user_id', '=', 'users.id')
            ->whereIn('wbd.selected_category', $categoryIds)
            ->distinct()
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @param array<int> $categoryIds
     * @param array<int>|null $selectedUserIds
     * @return array<int>
     */
    public function resolveTradeTargetUserIds(array $categoryIds, string $audienceMode, ?array $selectedUserIds = null): array
    {
        $eligibleIds = $this->eligibleWebUserIds($categoryIds);
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
        string $audienceMode
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
                    'role_id' => isset($rolesByUser[$userId]) ? (int) $rolesByUser[$userId] : null,
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
        }
    }

    private function resolveTradeNo(TradeQueriesINR $trade): string
    {
        $qid = trim((string) ($trade->queryId ?? ''));
        if ($qid !== '') {
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
