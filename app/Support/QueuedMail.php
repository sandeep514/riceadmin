<?php

namespace App\Support;

use App\Jobs\SendViewMailJob;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class QueuedMail
{
    /**
     * Queue a Blade/PHP mail view. When QUEUE_CONNECTION=sync, the send still
     * runs after the HTTP response so the frontend is not blocked by SMTP.
     *
     * @param  string|array<int, string>  $to
     */
    public static function send(
        string $view,
        array $viewData,
        string|array $to,
        string $subject,
        ?string $from = null,
        ?string $fromName = null,
        ?string $toName = null,
        ?string $attachmentPath = null,
        ?string $attachmentAs = null,
        ?string $attachmentMime = null,
    ): bool {
        if (is_array($to)) {
            $to = array_values(array_filter($to, fn ($address) => is_string($address) && trim($address) !== ''));
            if ($to === []) {
                return false;
            }
        } elseif (trim((string) $to) === '') {
            return false;
        }

        $subject = self::normalizeSntcSubject($subject);
        $fromName = self::normalizeSntcFromName($fromName);

        return self::dispatchJob(new SendViewMailJob(
            $view,
            $viewData,
            $to,
            $subject,
            $from,
            $fromName,
            $toName,
            $attachmentPath,
            $attachmentAs,
            $attachmentMime,
        ));
    }

    /**
     * Ensure brand "SNTC" is always uppercase in mail subjects.
     * Prefixes "SNTC " when the subject does not already include it.
     */
    public static function normalizeSntcSubject(string $subject): string
    {
        $subject = trim($subject);
        if ($subject === '') {
            return 'SNTC';
        }

        // Normalize any sntc / Sntc / etc. token to SNTC.
        $subject = preg_replace('/\bSNTC\b/i', 'SNTC', $subject) ?? $subject;

        if (! preg_match('/\bSNTC\b/', $subject)) {
            $subject = 'SNTC '.$subject;
        }

        return $subject;
    }

    public static function normalizeSntcFromName(?string $fromName): ?string
    {
        if ($fromName === null) {
            return null;
        }

        $fromName = trim($fromName);
        if ($fromName === '') {
            return $fromName;
        }

        return preg_replace('/\bSNTC\b/i', 'SNTC', $fromName) ?? $fromName;
    }

    public static function mailable(string|array $to, Mailable $mailable): bool
    {
        try {
            if (self::usesSyncQueue()) {
                dispatch(function () use ($to, $mailable) {
                    Mail::to($to)->send($mailable);
                })->afterResponse();
            } else {
                Mail::to($to)->queue($mailable);
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Queued mailable dispatch failed: '.$e->getMessage());

            return false;
        }
    }

    public static function dispatchJob(ShouldQueue $job): bool
    {
        try {
            if (self::usesSyncQueue()) {
                dispatch($job)->afterResponse();
            } else {
                dispatch($job);
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Queued mail job dispatch failed: '.$e->getMessage());

            return false;
        }
    }

    private static function usesSyncQueue(): bool
    {
        return config('queue.default', 'sync') === 'sync';
    }
}
