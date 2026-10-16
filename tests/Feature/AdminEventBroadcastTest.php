<?php

namespace Tests\Feature;

use App\Events\AdminEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Tests\TestCase;

/**
 * Regression: the dashboard "Send test notification" button appeared to do
 * nothing because AdminEvent broadcast on the PRIVATE channel
 * 'private-admin-events' while every client subscribes to the PUBLIC
 * 'admin-events' channel (docs/REACT_REVERB_SETUP.md). Those are different
 * channels, so nothing was ever received.
 */
class AdminEventBroadcastTest extends TestCase
{
    public function test_broadcasts_on_public_admin_events_channel(): void
    {
        $channels = (new AdminEvent('admin_notification', ['message' => 'hi']))->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(Channel::class, $channels[0]);
        $this->assertNotInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame('admin-events', $channels[0]->name);
    }

    public function test_broadcasts_synchronously_so_failures_surface(): void
    {
        $this->assertInstanceOf(
            ShouldBroadcastNow::class,
            new AdminEvent('admin_notification', ['message' => 'hi'])
        );
    }

    public function test_event_name_matches_client_listener(): void
    {
        // Clients listen with Echo as `.AdminEvent` (leading dot).
        $this->assertSame(
            'AdminEvent',
            (new AdminEvent('admin_notification', []))->broadcastAs()
        );
    }
}
