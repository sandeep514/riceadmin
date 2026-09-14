<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendViewMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 90;

    /**
     * @param  string|array<int, string>  $to
     */
    public function __construct(
        public string $view,
        public array $viewData,
        public string|array $to,
        public string $subject,
        public ?string $from = null,
        public ?string $fromName = null,
        public ?string $toName = null,
        public ?string $attachmentPath = null,
        public ?string $attachmentAs = null,
        public ?string $attachmentMime = null,
    ) {}

    public function handle(): void
    {
        $from = $this->from ?: config('mail.from.address');
        $fromName = $this->fromName ?: config('mail.from.name');
        $toName = $this->toName ?: '';
        $to = $this->to;
        $subject = $this->subject;
        $attachmentPath = $this->attachmentPath;
        $attachmentAs = $this->attachmentAs;
        $attachmentMime = $this->attachmentMime;

        Mail::send($this->view, $this->viewData, function ($message) use (
            $to,
            $toName,
            $subject,
            $from,
            $fromName,
            $attachmentPath,
            $attachmentAs,
            $attachmentMime
        ) {
            if (is_array($to)) {
                $message->to($to)->subject($subject);
            } else {
                $message->to($to, $toName)->subject($subject);
            }

            if ($from) {
                $message->from($from, $fromName);
            }

            if ($attachmentPath && is_file($attachmentPath)) {
                $message->attach($attachmentPath, [
                    'as' => $attachmentAs ?: basename($attachmentPath),
                    'mime' => $attachmentMime ?: 'application/octet-stream',
                ]);
            }
        });
    }
}
