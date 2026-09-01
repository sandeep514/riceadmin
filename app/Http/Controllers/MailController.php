<?php

namespace App\Http\Controllers;
use Mail;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class MailController extends Controller
    {


    public static function generateMail($mailTo,$mailFrom,$mailFromName,$mailMessage,$subject,$otp = null) {
        try {               
            $data = array('name'=>$otp);
            $respose = Mail::send('mail', $data, function($message) use ($mailTo, $mailMessage, $subject,$mailFrom,$mailFromName) {
                $message->to($mailTo, $mailMessage)->subject($subject);
                $message->from($mailFrom,$mailFromName);
            });
            return $respose;
        } catch (\Throwable $th) {
            //throw $th;
            dd($th);
        }
    }

    public static function generateMailForOTP($mailTo,$mailFrom,$mailFromName,$mailMessage,$subject,$otp) {
        try {               
            $data = array('name'=>$otp);
            $respose = Mail::send('otp', $data, function($message) use ($mailTo, $mailMessage, $subject,$mailFrom,$mailFromName) {
                $message->to($mailTo, $mailMessage)->subject($subject);
                $message->from($mailFrom,$mailFromName);
            });
            return $respose;

        } catch (\Throwable $th) {
            //throw $th;
            dd($th);
        }
    }

    public static function generateMailForOTPThanks($mailTo,$mailFrom,$mailFromName,$mailMessage,$subject,$otp) {
        try {               
            $data = array('name'=>$otp);
            $respose = Mail::send('otpThanks', $data, function($message) use ($mailTo, $mailMessage, $subject,$mailFrom,$mailFromName) {
                $message->to($mailTo, $mailMessage)->subject($subject);
                $message->from($mailFrom,$mailFromName);
            });
            return $respose;

        } catch (\Throwable $th) {
            //throw $th;
            dd($th);
        }
    }

    public static function sendContactUsMail($mailTo,$mailFrom,$mailFromName,$mailMessage,$subject,$data) {
        try {
            $respose = Mail::send('mail.contactUsMail', ['data' => $data], function($message) use ($mailTo, $mailMessage, $subject,$mailFrom,$mailFromName) {
                $message->to($mailTo, $mailMessage)->subject($subject);
                $message->from($mailFrom,$mailFromName);
            });
            return $respose;

        } catch (\Throwable $th) {
            //throw $th;
            dd($th);
        }
    }

    public static function sendBrandInterestMail($mailTo, $mailFrom, $mailFromName, $subject, $data)
    {
        try {
            return Mail::send('mail.brandInterestReceived', ['data' => $data], function ($message) use ($mailTo, $mailFrom, $mailFromName, $subject) {
                $message->to($mailTo, 'SNTC Enquiry')->subject($subject);
                $message->from($mailFrom, $mailFromName);
            });
        } catch (\Throwable $th) {
            \Log::warning('Brand interest mail failed: '.$th->getMessage());

            return false;
        }
    }

    public static function sendWebBrandCreatedMail($mailTo, $mailFrom, $mailFromName, $subject, $data)
    {
        try {
            return Mail::send('mail.webBrandCreated', ['data' => $data], function ($message) use ($mailTo, $mailFrom, $mailFromName, $subject) {
                $message->to($mailTo, 'SNTC Enquiry')->subject($subject);
                $message->from($mailFrom, $mailFromName);
            });
        } catch (\Throwable $th) {
            \Log::warning('Web brand created mail failed: '.$th->getMessage());

            return false;
        }
    }

    public static function sendVendorProductVariantsMail($mailTo, $mailFrom, $mailFromName, $subject, $data)
    {
        try {
            return Mail::send('mail.vendorProductVariantsSubmitted', ['data' => $data], function ($message) use ($mailTo, $mailFrom, $mailFromName, $subject) {
                $message->to($mailTo, 'SNTC Enquiry')->subject($subject);
                $message->from($mailFrom, $mailFromName);
            });
        } catch (\Throwable $th) {
            \Log::warning('Vendor product variants mail failed: '.$th->getMessage());

            return false;
        }
    }

    public static function html_email($file, $from , $to , $data = []) {
        try {
            $respose = Mail::send($file, ['data' => $data], function($message) use ($from , $to) {
                $message->to($to, 'SNTC')->subject('notifications');
                $message->from($from,'SNTC');
            });
        } catch (\Throwable $th) {
            //throw $th;
            dd($th);
        }
    }
}
