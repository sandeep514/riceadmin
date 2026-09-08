<?php

namespace App\Services;

use App\Mail\WelcomeRegistrationMail;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WelcomeRegistrationMailService
{
    /**
     * Send SNTC welcome email with Terms & Conditions PDF (if available).
     */
    public static function send(User $user): bool
    {
        $email = trim((string) ($user->email ?? ''));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            $termsPdfPath = app(SitePolicyPdfService::class)->absolutePathForTerms();

            Mail::to($email)->send(new WelcomeRegistrationMail(
                userName: (string) ($user->name ?: 'User'),
                userEmail: $email,
                termsPdfAbsolutePath: $termsPdfPath
            ));

            return true;
        } catch (\Throwable $e) {
            Log::error('Welcome registration mail failed: '.$e->getMessage(), [
                'user_id' => $user->id,
                'email' => $email,
            ]);

            return false;
        }
    }
}
