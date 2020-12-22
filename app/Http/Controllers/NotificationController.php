<?php

namespace App\Http\Controllers;

use App\DataTables\SamplesDataTable;
use App\Http\Requests\SampleRequest;
use App\Sample;
use App\Notification;
use App\Services\SampleService;
use Illuminate\Http\Request;
use Session;
use App\User;

class NotificationController extends Controller
{
    public function index()
    {
        return View('notification.index');
    }
    public function sendNotification(Request $request)
    {
        // dd($request->userType);
        $userData = User::whereIn('role' , $request->userType)->get();
        $request->validate([
                'userType' => 'required|array',
                'message' => 'required'
            ]);
        foreach( $userData as $k => $v ){
            if( $v != null ){
                if( $v->user_token != null ){
                    $this->sendNotif( $request->message, $v->user_token );
                    Notification::create([
                            'user_id' => $v->id,
                            'message' => $request->message
                    ]);       
                }
                             
            }
        }
        return back();
    }
    
    public function sendNotif( $message , $token ){
        $url = "https://fcm.googleapis.com/fcm/send";

        $serverKey = 'AAAA3Rpauec:APA91bGCGLlfPVMmbEOW4AGmb6osPCZtpqoNIZgLUmr8bgbQezWGkIrTBaHMMqUYLj9EeAl_BPcF1f96MxE7ZEmUg0rfGrVLmB7wFPFgCj0sTLyZQDaZgMwZgTAOH5AsOnN1g9lxprTY';
        $title = "Message";
        $body = $message;
        $notification = array('title' =>$title, 'body' => $body, 'sound' => 'default', 'badge' => '1');
        $arrayToSend = array('to' => $token, 'notification' => $notification,'priority'=>'high');
        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='. $serverKey;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);

        $response = curl_exec($ch);
       
        if ($response === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
         return $response;
    }

}