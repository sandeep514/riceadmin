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
use App\HotDealNotification;
use App\QualityMaster;
use App\USD_defaultmaster;
use App\HotDealAccept;
use Carbon\Carbon;

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
            'userAppType'=> 'required',
        ]);

        if( $request->userAppType == 'usd' ){
            $users = User::whereIn('usd_role', $request->userType)->where('id' , '!=' , 301 )->where('user_token' , '!=' , null)->pluck('user_token','id');
        }else{
            $users = User::whereIn('role', $request->userType)->where('id' , '!=' , 301 )->where('user_token' , '!=' , null)->pluck('user_token','id');
        }
        // return true;

        // $users = User::whereIn('id', [2185,220,2176])->where('user_token' , '!=' , null)->pluck('user_token' , 'id');
        // $users = User::whereIn('id', [220,1297,1664,2173])->where('user_token' , '!=' , null)->get();

        $arrayUsers = $users->toArray();

        $registeredTokens =  array_chunk($arrayUsers , 400);
        

        $postedData = [];
        $arrayFilters = [];
        
        if($users->count() > 0){
            $arrayFilters = array_filter($users->toArray());
        }


        foreach( $arrayFilters as $k => $v ){
            // $this->sendNotif($request->title, $request->message, $v,$request->userAppType);
            // Notification::create([
            //         'user_id' => $k,
            //         'title' => $request->title,
            //         'message' => $request->message,
            //         'userAppType' => $request->userAppType,
            //         'status' => 1
            // ]);
            $postedData[] = [
                    'user_id' => $k,
                    'title' => $request->title,
                    'message' => $request->message,
                    'userAppType' => $request->userAppType,
                    'status' => 1
            ];
        }
        Notification::insert($postedData);
        foreach($registeredTokens as $k => $v){
            echo $this->sendNotifMultiple($request->title, $request->message, $v,$request->userAppType);
        }
        
        return back();

        // $request->validate([
        //         'userType' => 'required|array',
        //         'message' => 'required'
        //     ]);

        // $checkIfUserHasSameNotif = Notification::where(['title' => $request->title,'message' => $request->message])->where('created_at', 'like', date("Y-m-d")."%")->get()->map(function ($query) {
        //     return $query->user_id;
        // });
        // $arrayFilter = [];
        // if( $checkIfUserHasSameNotif != null ){
        //     $arrayFilter = array_filter($checkIfUserHasSameNotif->toArray());   
        // }

        // foreach ($users as $key => $user) {
        //     if ($user != null) {
        //         if ($user->user_token != null) {
        //             if( !in_array($user->id, $arrayFilter) ){
        //                 if( $checkIfUserHasSameNotif->count() == 0 ){
        //                     echo($this->sendNotif($request->title, $request->message, $user->user_token));
        //                     Notification::create([
        //                             'user_id' => $user->id,
        //                             'title' => $request->title,
        //                             'message' => $request->message
        //                     ]);
        //                 }
        //             }
        //         }
        //     }
        // }
        return back();
    }
    
    public function sendNotif($title, $message, $token , $payload = null)
    {
        $url = "https://fcm.googleapis.com/fcm/send";

        $serverKey = 'AAAA10hB_8I:APA91bHVSnAJjacznL6i3p9dWnKvJeceYJlTbwt_rvyq6Nx8tOPsMlxtYPqHzAJRAazC5JJof9PZHaw_uo1qbNkKK4YgJLKN_39ozcIlbCpt3YQ36Y5rT6ftegC0nnEiOZ-dYsYqFWcV';
        $body = $message;
        $notification = ['title' => $title, 'body' => $body, 'sound' => 'default', 'badge' => '1' ];
        
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
                    'data' => [ 'notification_forground' => 'true','extra' => [ 'hotDealId' => $payload]],
                    'notification' => $notification,
                    'priority'=>'high'
                ];

        $json = json_encode($arrayToSend);
        $headers = [];
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='. $serverKey;
        $ch = curl_init();


        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        $response = curl_exec($ch);
       
        if ($response === false) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $response;
    }
    public function sendNotifMultiple($title, $message, $tokens , $payload = null)
    {
        $url = "https://fcm.googleapis.com/fcm/send";

        $serverKey = 'AAAA10hB_8I:APA91bHVSnAJjacznL6i3p9dWnKvJeceYJlTbwt_rvyq6Nx8tOPsMlxtYPqHzAJRAazC5JJof9PZHaw_uo1qbNkKK4YgJLKN_39ozcIlbCpt3YQ36Y5rT6ftegC0nnEiOZ-dYsYqFWcV';
        $body = $message;
        $notification = ['title' => $title, 'body' => $body, 'sound' => 'default', 'badge' => '1' ];
        
        $apns = ['payload' => [ 
                    'aps' => [
                        'sound' => 'default' ,
                        'badge' => 1 ,
                        'content-available' => 1
                    ]
                    ]
                ];
        
        $arrayToSend = [
                    'registration_ids' => $tokens,
                    'apns' => $apns,
                    'data' => [ 'notification_forground' => 'true','extra' => [ 'hotDealId' => $payload]],
                    'notification' => $notification,
                    'priority'=>'high'
                ];

        $json = json_encode($arrayToSend);
        $headers = [];
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='. $serverKey;
        $ch = curl_init();


        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

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
    public function hotDealIndex()
    {
        $todayDate = Carbon::now()->format('Y-m-d');
        $hotDeal = HotDealNotification::with(['getUSDDefaultMaster','getRiceQuality'])->orderBy('id' , 'desc')->get();
        $packing = USD_defaultmaster::get();
        $quality = QualityMaster::get();

        return view('hotdeals.index' , compact('packing' , 'quality' ,'todayDate' , 'hotDeal'));
    }

    public function hotDealPushNotification(Request $request)
    {
        $attachmentOneName= '';
        $attachmentTwoName= '';

        if ($request->hasFile('attachment1')) {
            $image = $request->file('attachment1');
            $attachmentOneName = time().''.rand('1000' , '9999').'.'.$image->getClientOriginalExtension();            
            $destinationPath = public_path('/uploads/notifications/');
            $image->move($destinationPath, $attachmentOneName);
        }

        if ($request->hasFile('attachment2')) {
            $image = $request->file('attachment2');
            $attachmentTwoName = time().''.rand('1000' , '9999').'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/notifications/');
            $image->move($destinationPath, $attachmentTwoName);
        }
        $request->validate([
            "title" => "required",
            "quality" => "required",
            "packing" => "required",
            "fob" => "required",
            "qty" => "required",
            "validdate" => "required",
            "message" => "required",
           

        ]);

        $hotdeal = HotDealNotification::create([   
            "title" => $request->title,
            "quality" => $request->quality,
            "packing" => $request->packing,
            "fob" => $request->fob,
            "qty" => $request->qty,
            "validdate" => $request->validdate,
            "message" => $request->message,
            'attachment1' => $attachmentOneName,
            'attachment2' => $attachmentTwoName,
            'status' => 1,
            'length' => ($request->has('Length'))? $request->Length : 0 ,
            'purity' => ($request->has('Purity'))? $request->Purity : 0 ,
            'moisture' => ($request->has('Moisture'))? $request->Moisture : 0 ,
            'broken' => ($request->has('Broken'))? $request->Broken : 0 ,
            'kett' => ($request->has('Kett'))? $request->Kett : 0 ,
            'dd' => ($request->has('DDs'))? $request->DDs : 0
        ]);

        $users = User::select('id' , 'user_token')->where('role', 5)->where('country' , '!=' , null )->where('country' , '!=', 'india' )->get();

        foreach ($users as $key => $user) {
            if ($user != null) {
                if ($user->user_token != null) {
                    echo($this->sendNotif($request->title, $request->message, $user->user_token , $hotdeal->id));
                    // HotDealAccept::create([
                    //         'hotdeal_id' => $hotdeal->id,
                    //         'buyer_id' => $user->id,
                    //         'status' => 1
                    // ]);
                }
            }
        }
        return redirect()->route('hot.deal.notification.master');
    }
    public function updateHotDealStatus($statusToken , $hotDealNotifId)
    {
        HotDealNotification::where('id' , $hotDealNotifId)->update(['status' => $statusToken]);
        return back();

    }
}