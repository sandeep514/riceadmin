<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;
use Mail;

class MailHelper
{
    public function generateMail($mailTo,$mailFrom,$mailFromName,$mailMessage,$subject) {
        dd('helper function callled');
        try {            
            $data = ['mailMessage' => $message];
            $respose = Mail::send('mail', $data, function($message) {
                $message->to($mailTo, $mailMessage)->subject($subject);
                $message->from($mailFrom,$mailFromName);
            });
            dump($respose);
        } catch (\Throwable $th) {
            //throw $th;
            dd($th);
        }
    }
}