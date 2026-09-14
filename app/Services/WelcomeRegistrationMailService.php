<?php

namespace App\Services;

use App\Jobs\SendWelcomeRegistrationMailJob;
use App\Mail\WelcomeRegistrationMail;
use App\Support\QueuedMail;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WelcomeRegistrationMailService
{
    /**
     * Queue SNTC welcome email with Terms & Conditions PDF attached when available.
     */
    public static function send(User $user): bool
    {
        $email = trim((string) ($user->email ?? ''));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return QueuedMail::dispatchJob(new SendWelcomeRegistrationMailJob((int) $user->id));
    }

    /**
     * Send the welcome mail immediately (used by the queue worker).
     */
    public static function sendNow(User $user): bool
    {
        $email = trim((string) ($user->email ?? ''));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            $pdfService = app(SitePolicyPdfService::class);
            $termsPdfPath = $pdfService->absolutePathForTerms();
            $termsPdfBinary = null;

            if (! $termsPdfPath || ! is_file($termsPdfPath)) {
                $termsPdfBinary = $pdfService->termsPdfBinary();
                $termsPdfPath = null;
            }

            if (! $termsPdfPath && (! is_string($termsPdfBinary) || $termsPdfBinary === '')) {
                Log::warning('Welcome registration mail sending without Terms PDF attachment.', [
                    'user_id' => $user->id,
                    'email' => $email,
                ]);
            }

            Mail::to($email)->send(new WelcomeRegistrationMail(
                (string) ($user->name ?: 'User'),
                $email,
                $termsPdfPath,
                $termsPdfBinary
            ));

            return true;
        } catch (\Throwable $e) {
            Log::error('Welcome registration mail failed: '.$e->getMessage(), [
                'user_id' => $user->id,
                'email' => $email,
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }
}
