<?php

namespace App\Http\Controllers;

use App\Support\QueuedMail;
use Illuminate\Support\Facades\Log;

class MailController extends Controller
{
    public static function generateMail($mailTo, $mailFrom, $mailFromName, $mailMessage, $subject, $otp = null)
    {
        return QueuedMail::send(
            'mail',
            ['name' => $otp],
            $mailTo,
            $subject,
            $mailFrom,
            $mailFromName,
            $mailMessage
        );
    }

    public static function generateMailForOTP($mailTo, $mailFrom, $mailFromName, $mailMessage, $subject, $otp)
    {
        return QueuedMail::send(
            'otp',
            ['name' => $otp],
            $mailTo,
            $subject,
            $mailFrom,
            $mailFromName,
            $mailMessage
        );
    }

    public static function generateMailForOTPThanks($mailTo, $mailFrom, $mailFromName, $mailMessage, $subject, $otp)
    {
        return QueuedMail::send(
            'otpThanks',
            ['name' => $otp],
            $mailTo,
            $subject,
            $mailFrom,
            $mailFromName,
            $mailMessage
        );
    }

    public static function sendContactUsMail($mailTo, $mailFrom, $mailFromName, $mailMessage, $subject, $data)
    {
        return QueuedMail::send(
            'mail.contactUsMail',
            ['data' => $data],
            $mailTo,
            $subject,
            $mailFrom,
            $mailFromName,
            $mailMessage
        );
    }

    public static function sendBrandInterestMail($mailTo, $mailFrom, $mailFromName, $subject, $data)
    {
        return QueuedMail::send(
            'mail.brandInterestReceived',
            ['data' => $data],
            $mailTo,
            $subject,
            $mailFrom,
            $mailFromName,
            'SNTC Enquiry'
        );
    }

    public static function sendWebBrandCreatedMail($mailTo, $mailFrom, $mailFromName, $subject, $data)
    {
        return QueuedMail::send(
            'mail.webBrandCreated',
            ['data' => $data],
            $mailTo,
            $subject,
            $mailFrom,
            $mailFromName,
            'SNTC Enquiry'
        );
    }

    public static function sendVendorProductVariantsMail($mailTo, $mailFrom, $mailFromName, $subject, $data)
    {
        return QueuedMail::send(
            'mail.vendorProductVariantsSubmitted',
            ['data' => $data],
            $mailTo,
            $subject,
            $mailFrom,
            $mailFromName,
            'SNTC Enquiry'
        );
    }

    public static function sendVendorProductAcceptedMail($mailTo, $mailFrom, $mailFromName, $subject, $data)
    {
        $toName = $data['userName'] ?? 'Vendor';

        return QueuedMail::send(
            'mail.vendorProductAccepted',
            ['data' => $data],
            $mailTo,
            $subject,
            $mailFrom,
            $mailFromName,
            $toName
        );
    }

    public static function sendVendorProductDeactivatedMail($mailTo, $mailFrom, $mailFromName, $subject, $data)
    {
        $toName = $data['userName'] ?? 'Vendor';

        return QueuedMail::send(
            'mail.vendorProductDeactivated',
            ['data' => $data],
            $mailTo,
            $subject,
            $mailFrom,
            $mailFromName,
            $toName
        );
    }

    public static function sendVendorProductNeedsUpdateMail($mailTo, $mailFrom, $mailFromName, $subject, $data)
    {
        $toName = $data['userName'] ?? 'Vendor';

        return QueuedMail::send(
            'mail.vendorProductNeedsUpdate',
            ['data' => $data],
            $mailTo,
            $subject,
            $mailFrom,
            $mailFromName,
            $toName
        );
    }

    public static function html_email($file, $from, $to, $data = [])
    {
        try {
            return QueuedMail::send(
                $file,
                ['data' => $data],
                $to,
                'notifications',
                $from,
                'SNTC',
                'SNTC'
            );
        } catch (\Throwable $th) {
            Log::warning('Queued html email failed: '.$th->getMessage());

            return false;
        }
    }
}
