<?php

namespace App\Listeners;

use App\Events\WebPortalNotificationEvent;
use App\Jobs\SendPushNotificationJob;
use App\User;

/**
 * When a portal notification is broadcast on Reverb (web-user.{id}),
 * also send FCM so the same user logged in on the mobile app gets a push.
 *
 * Runs inline (not queued) so FCM still sends when no queue worker is running.
 */
class SendFcmForWebPortalNotification
{
    public function handle(WebPortalNotificationEvent $event): void
    {
        $notification = $event->notification;
        $userId = (int) $notification->user_id;

        $user = User::query()
            ->where('id', $userId)
            ->whereNotNull('user_token')
            ->where('user_token', '!=', '')
            ->first(['id', 'user_token']);

        if (! $user) {
            return;
        }

        SendPushNotificationJob::dispatchSync(
            (string) $notification->title,
            (string) $notification->message,
            [
                [
                    'id' => (int) $user->id,
                    'user_token' => (string) $user->user_token,
                ],
            ],
            'portal',
            false,
            [
                'type' => 'portal_notification',
                'notification_id' => (string) $notification->id,
                'user_id' => (string) $userId,
            ]
        );
    }
}
