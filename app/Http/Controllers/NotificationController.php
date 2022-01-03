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
        $request->validate([
            'userType'=> 'required',
            'title'=> 'required',
            'message'=> 'required',
        ]);
        // dd($request->userType);
        //$users = User::whereIn('id', [272,219,224,186,378])->get();

        $users = User::whereIn('role', $request->userType)->get();
        $request->validate([
                'userType' => 'required|array',
                'message' => 'required'
            ]);
        foreach ($users as $key => $user) {
            if ($user != null) {
                if ($user->user_token != null) {
                    echo($this->sendNotif($request->title, $request->message, $user->user_token));
                    Notification::create([
                            'user_id' => $user->id,
                            'title' => $request->title,
                            'message' => $request->message
                    ]);
                }
            }
        }
        return back();
    }
    
    public function sendNotif($title, $message, $token)
    {
        $url = "https://fcm.googleapis.com/fcm/send";

        $serverKey = 'AAAA10hB_8I:APA91bHVSnAJjacznL6i3p9dWnKvJeceYJlTbwt_rvyq6Nx8tOPsMlxtYPqHzAJRAazC5JJof9PZHaw_uo1qbNkKK4YgJLKN_39ozcIlbCpt3YQ36Y5rT6ftegC0nnEiOZ-dYsYqFWcV';
        $body = $message;
        $notification = ['title' => $title, 'body' => $body, 'sound' => 'default', 'badge' => '1'];
        
        $apns = ['payload' => [ 
                    'aps' => [
                        'sound' => 'default' ,
                        'badge' => 1 ,
                        'content-available' => 1
                    ]
                    ]
                ];
        
        $arrayToSend = [
                'to' => $token,
                'apns' => $apns,
                'notification' => $notification,
                'data' => 
                    [ 'notification_forground' => 'true'],
                    'notification' => $notification,
                    'priority'=>'high'
                ];

        $json = json_encode($arrayToSend);
        $headers = [];
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='. $serverKey;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
       
        if ($response === false) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $response;
    }

    public function getUserNotifications($user_id = null)
    {
        if (is_null($user_id)) {
            return response()->json(['status'=>false,'message'=>'Required Parameters Missing !'], 200);
        }

        $notifications = Notification::where('user_id', $user_id)->latest()->take(50)->get();
        
        if ($notifications->count() <=0) {
            return response()->json(['status'=>false,'message'=>'User not found !'], 200);
        }
        return response()->json(['status'=>true,'data'=> $notifications->toArray()], 200);
    }
}
