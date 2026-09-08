<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $userEmail,
        public ?string $termsPdfAbsolutePath = null,
        public ?string $termsPdfBinary = null,
    ) {}

    public function build()
    {
        $mail = $this->from('info@sntcgroup.com', 'SNTC Team - India')
            ->subject('Welcome to SNTC')
            ->view('mail.welcomeRegistration')
            ->with([
                'userName' => $this->userName,
                'userEmail' => $this->userEmail,
            ]);

        if ($this->termsPdfAbsolutePath && is_file($this->termsPdfAbsolutePath)) {
            $mail->attach($this->termsPdfAbsolutePath, [
                'as' => 'SNTC-Terms-and-Conditions.pdf',
                'mime' => 'application/pdf',
            ]);
        } elseif (is_string($this->termsPdfBinary) && $this->termsPdfBinary !== '') {
            $mail->attachData($this->termsPdfBinary, 'SNTC-Terms-and-Conditions.pdf', [
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
