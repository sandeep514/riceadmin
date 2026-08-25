<?php

namespace App\Jobs;

use App\Notification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of seconds before the job times out.
     */
    public int $timeout = 120;

    /**
     * Number of times to retry the job on failure.
     */
    public int $tries = 3;

    /**
     * @param  string  $title
     * @param  string  $body
     * @param  array  $users  Array of ['id'=>..., 'user_token'=>...]
     * @param  string  $userAppType
     * @param  bool  $persistToNotificationTable  Set false when caller already wrote `notification` rows.
     * @param  array<string, string>  $data  Optional FCM data payload (e.g. type=portal_notification).
     * @param  string|null  $batchKey  Stable id so retries do not re-insert inbox rows.
     */
    public function __construct(
        public string $title,
        public string $body,
        public array $users,
        public string $userAppType,
        public bool $persistToNotificationTable = true,
        public array $data = [],
        public ?string $batchKey = null
    ) {
        if ($this->batchKey === null || $this->batchKey === '') {
            $userIds = array_map(static fn ($u) => (string) ($u['id'] ?? ''), $this->users);
            sort($userIds);
            $this->batchKey = hash(
                'sha256',
                $this->title.'|'.$this->body.'|'.$this->userAppType.'|'.implode(',', $userIds)
            );
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fcmCacheKey = 'push_notification_fcm_sent:'.$this->batchKey;
        $persistCacheKey = 'push_notification_persisted:'.$this->batchKey;
        $alreadySentFcm = Cache::has($fcmCacheKey);
        $alreadyPersisted = Cache::has($persistCacheKey);

        $tokenToUserIds = [];
        foreach ($this->users as $user) {
            $token = trim((string) ($user['user_token'] ?? ''));
            if ($token === '') {
                continue;
            }
            $tokenToUserIds[$token][] = (int) $user['id'];
        }
        $tokens = array_keys($tokenToUserIds);

        $successfulUserIds = [];

        if ($tokens === []) {
            // No FCM tokens — still record inbox once when requested.
            $successfulUserIds = array_values(array_unique(array_map(
                static fn ($u) => (int) ($u['id'] ?? 0),
                $this->users
            )));
            $successfulUserIds = array_values(array_filter($successfulUserIds, static fn ($id) => $id > 0));
            Cache::put($fcmCacheKey, 1, now()->addDay());
        } elseif ($alreadySentFcm) {
            // Prior attempt already delivered FCM; only finish inbox persistence if needed.
            $successfulUserIds = array_values(array_unique(array_map(
                static fn ($u) => (int) ($u['id'] ?? 0),
                $this->users
            )));
            $successfulUserIds = array_values(array_filter($successfulUserIds, static fn ($id) => $id > 0));
        } else {
            try {
                $messaging = Firebase::messaging();

                $message = CloudMessage::new()
                    ->withNotification([
                        'title' => $this->title,
                        'body' => $this->body,
                    ]);

                if ($this->data !== []) {
                    $message = $message->withData(
                        array_map(static fn ($value) => (string) $value, $this->data)
                    );
                }

                $response = $messaging->sendMulticast($message, array_values($tokens));

                foreach ($response->validTokens() as $token) {
                    if (! isset($tokenToUserIds[$token])) {
                        continue;
                    }
                    foreach ($tokenToUserIds[$token] as $userId) {
                        $successfulUserIds[] = $userId;
                    }
                }

                Log::info("Push notification chunk sent. Success: {$response->successes()->count()}, Failures: {$response->failures()->count()}");

                if ($response->successes()->count() === 0 && $response->failures()->count() > 0) {
                    throw new \RuntimeException('FCM multicast failed for all tokens in chunk.');
                }

                Cache::put($fcmCacheKey, 1, now()->addDay());
            } catch (\Throwable $e) {
                Log::error('SendPushNotificationJob failed: '.$e->getMessage());
                throw $e;
            }
        }

        if ($this->persistToNotificationTable && ! $alreadyPersisted) {
            $this->persistNotificationRows(array_values(array_unique($successfulUserIds)));
            Cache::put($persistCacheKey, 1, now()->addDay());
        }
    }

    /**
     * @param  array<int>  $userIds
     */
    private function persistNotificationRows(array $userIds): void
    {
        if ($userIds === []) {
            return;
        }

        $now = Carbon::now()->format('Y-m-d H:i:s');
        $postedData = [];
        foreach ($userIds as $userId) {
            $postedData[] = [
                'user_id' => $userId,
                'title' => $this->title,
                'message' => $this->body,
                'userAppType' => $this->userAppType,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Notification::insert($postedData);
    }
}
