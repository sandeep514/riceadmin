<?php

namespace App\Events;

use App\WebUserNotification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebPortalNotificationEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $extra  Extra payload fields (e.g. type, trade_id)
     */
    public function __construct(
        public WebUserNotification $notification,
        public array $extra = []
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('web-user.' . $this->notification->user_id)];
    }

    public function broadcastAs(): string
    {
        return 'WebUserNotification';
    }

    public function broadcastWith(): array
    {
        $notifyDate = $this->notification->notify_date;
        if ($notifyDate instanceof \DateTimeInterface) {
            $notifyDate = $notifyDate->format('Y-m-d');
        } elseif ($notifyDate !== null) {
            $notifyDate = (string) $notifyDate;
        }

        $payload = [
            'id' => $this->notification->id,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'notify_date' => $notifyDate,
            'role_id' => $this->notification->role_id,
            'category_id' => $this->notification->category_id,
            'broadcast_group_id' => $this->notification->broadcast_group_id,
            'user_id' => $this->notification->user_id,
        ];

        foreach ($this->extra as $key => $value) {
            if ($value === null) {
                continue;
            }
            // FCM/socket clients expect string data values for common keys.
            $payload[$key] = is_scalar($value) ? (string) $value : $value;
        }

        return $payload;
    }
}
