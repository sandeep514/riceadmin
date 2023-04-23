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

    public static function html_email($file, $from , $to , $data = []) {
        try {            
            $respose = Mail::send($file, $data, function($message) use ($from , $to) {
                $message->to($to, 'SNTC')->subject('notifications');
                $message->from($from,'SNTC');
            });
        } catch (\Throwable $th) {
            //throw $th;
            dd($th);
        }
    }
}
