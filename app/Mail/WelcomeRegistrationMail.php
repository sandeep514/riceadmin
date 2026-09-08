<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $userEmail,
        public ?string $termsPdfAbsolutePath = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('info@sntcgroup.com', 'SNTC Team - India'),
            subject: 'Welcome to SNTC',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.welcomeRegistration',
            with: [
                'userName' => $this->userName,
                'userEmail' => $this->userEmail,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->termsPdfAbsolutePath || ! is_file($this->termsPdfAbsolutePath)) {
            return [];
        }

        return [
            Attachment::fromPath($this->termsPdfAbsolutePath)
                ->as('SNTC-Terms-and-Conditions.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
