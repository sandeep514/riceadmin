<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Notifies admin (info@sntcgroup.com) when a new web user completes trial signup flow.
 */
class NewUserRegistrationAdminMail extends Mailable implements ShouldQueue
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
            subject: 'New User Registration-Webversion',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.newUserAdded',
            with: [
                'userName' => $this->userName,
                'userEmail' => $this->userEmail,
            ],
        );
    }
}
