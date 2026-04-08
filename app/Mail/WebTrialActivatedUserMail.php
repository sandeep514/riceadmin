<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WebTrialActivatedUserMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $userEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('info@sntcgroup.com', 'SNTC Team - India'),
            subject: 'Your SNTC 30-Day Free Trial is Now Active.',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.sendTrailMailToUser',
            with: [
                'userName' => $this->userName,
                'userEmail' => $this->userEmail,
            ],
        );
    }
}
