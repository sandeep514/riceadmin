<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Event type (e.g. 'price_updated', 'plan_key_updated') for the React app to filter.
     */
    public string $type;

    /**
     * Payload sent to the frontend.
     */
    public array $payload;

    public function __construct(string $type, array $payload = [])
    {
        $this->type = $type;
        $this->payload = $payload;
    }

    /**
     * Private channel for admin events (Echo: private('admin-events')).
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin-events')];
    }

    /**
     * Event name the React app must listen for (e.g. .AdminEvent in Echo).
     */
    public function broadcastAs(): string
    {
        return 'AdminEvent';
    }

    /**
     * Data sent as the event payload. React receives this as the event data.
     */
    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'payload' => $this->payload,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
