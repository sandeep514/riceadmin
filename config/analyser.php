<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Analyser portal login URL
    |--------------------------------------------------------------------------
    | Sent in the welcome/OTP mails to analyser clients (historical price
    | viewer/downloader). Placeholder until the portal URL is finalised.
    */
    'login_url' => env('ANALYSER_LOGIN_URL', 'abc'),

    // Digits for the emailed OTP used for analyser login (no password).
    'otp_digits' => 6,
];
