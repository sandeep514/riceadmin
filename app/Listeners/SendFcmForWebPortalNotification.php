<?php

namespace App\Listeners;

use App\Events\WebPortalNotificationEvent;

/**
 * Previously sent FCM when WebPortalNotificationEvent was broadcast.
 *
 * FCM is now sent explicitly in WebPortalNotificationDelivery so socket
 * failures never block push, and so push is not double-sent.
 * Listener kept as a no-op for backward compatibility with EventServiceProvider.
 */
class SendFcmForWebPortalNotification
{
    public function handle(WebPortalNotificationEvent $event): void
    {
        // Intentionally empty — see WebPortalNotificationDelivery::deliverToUsers.
    }
}
