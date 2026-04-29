<?php

namespace App\Jobs;

use App\Notification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
     * @param string $title
     * @param string $body
     * @param array  $users  Array of ['id'=>..., 'user_token'=>...]
     * @param string $userAppType
     */
    public function __construct(
        public string $title,
        public string $body,
        public array  $users,
        public string $userAppType
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Save notification records for this chunk
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $postedData = [];
        foreach ($this->users as $user) {
            $postedData[] = [
                'user_id'     => $user['id'],
                'title'       => $this->title,
                'message'     => $this->body,
                'userAppType' => $this->userAppType,
                'status'      => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }
        if (!empty($postedData)) {
            Notification::insert($postedData);
        }

        // 2. Send FCM multicast for this chunk
        $tokens = array_filter(
            array_column($this->users, 'user_token')
        );

        if (empty($tokens)) {
            return;
        }

        try {
            $messaging = Firebase::messaging();

            $message = CloudMessage::new()
                ->withNotification([
                    'title' => $this->title,
                    'body'  => $this->body,
                ]);

            $response = $messaging->sendMulticast($message, array_values($tokens));

            Log::info("Push notification chunk sent. Success: {$response->successes()->count()}, Failures: {$response->failures()->count()}");
        } catch (\Throwable $e) {
            Log::error('SendPushNotificationJob failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
