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

    public function html_email() {
        try {            
            $data = array('name'=>"Virat Gandhi");
            $respose = Mail::send('mail', $data, function($message) {
                $message->to('jaskaransingh4704@gmail.com', 'Test Mail')->subject('Laravel HTML Testing Mail');
                $message->from('jaskaransingh5530@gmail.com','Jaskaran Songh');
            });
            dump($respose);
            // echo "HTML Email Sent. Check your inbox.";
        } catch (\Throwable $th) {
            //throw $th;
            dd($th);
        }
    }
}
