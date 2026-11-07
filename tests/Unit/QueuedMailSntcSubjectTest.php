<?php

namespace Tests\Unit;

use App\Support\QueuedMail;
use PHPUnit\Framework\TestCase;

class QueuedMailSntcSubjectTest extends TestCase
{
    public function test_prefixes_subject_when_sntc_missing(): void
    {
        $this->assertSame('SNTC Account on Hold', QueuedMail::normalizeSntcSubject('Account on Hold'));
        $this->assertSame('SNTC Paddy Prices Export', QueuedMail::normalizeSntcSubject('Paddy Prices Export'));
    }

    public function test_normalizes_existing_sntc_casing(): void
    {
        $this->assertSame('Welcome to SNTC', QueuedMail::normalizeSntcSubject('Welcome to sntc'));
        $this->assertSame('Sell with SNTC', QueuedMail::normalizeSntcSubject('Sell with Sntc'));
        $this->assertSame('Your SNTC 30-Day Free Trial is Now Active.', QueuedMail::normalizeSntcSubject('Your SNTC 30-Day Free Trial is Now Active.'));
    }

    public function test_does_not_double_prefix(): void
    {
        $this->assertSame('SNTC OTP Verification', QueuedMail::normalizeSntcSubject('SNTC OTP Verification'));
    }
}
