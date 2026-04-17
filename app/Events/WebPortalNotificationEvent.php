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

    public function __construct(public WebUserNotification $notification)
    {
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
        return [
            'id' => $this->notification->id,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'notify_date' => $this->notification->notify_date?->format('Y-m-d'),
            'role_id' => $this->notification->role_id,
            'category_id' => $this->notification->category_id,
            'broadcast_group_id' => $this->notification->broadcast_group_id,
        ];
    }
}
