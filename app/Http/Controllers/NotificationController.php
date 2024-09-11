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
use App\Notifications\SNTCNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index()
    {
        return View('notification.index');
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'userType' => 'required',
            'title' => 'required',
            'message' => 'required',
            'userAppType' => 'required',
        ]);

        if ($request->userAppType == 'usd') {
            $users = User::query()
                ->whereIn('usd_role', $request->userType)
                ->where('id', '!=', 301)
                ->where('user_token', '!=', null)
                ->select('user_token', 'id')
                ->get();
        } else {

            $users = User::query()
                ->whereIn('role', $request->userType)
                ->where('id', '!=', 301)
                ->where('user_token', '!=', null)
                ->select('user_token', 'id')
                ->get();
        }
        // return true;

        // $users = User::query()
        //     ->whereIn('id', [224])
        //     ->whereNotNull('user_token')
        //     ->select('user_token', 'id')
        //     ->get();
        // $users = User::whereIn('id', [220,1297,1664,2173])->where('user_token' , '!=' , null)->get();

        // $arrayUsers = $users->toArray();

        // $registeredTokens =  array_chunk($arrayUsers, 400);


        $postedData = [];
        // $arrayFilters = [];

        // if ($users->count() > 0) {
        //     $arrayFilters = array_filter($users->toArray());
        // }


        foreach ($users as $user) {
            // $this->sendNotif($request->title, $request->message, $v,$request->userAppType);
            // Notification::create([
            //         'user_id' => $k,
            //         'title' => $request->title,
            //         'message' => $request->message,
            //         'userAppType' => $request->userAppType,
            //         'status' => 1
            // ]);
            $postedData[] = [
                'user_id' => $user->id,
                'title' => $request->title,
                'message' => $request->message,
                'userAppType' => $request->userAppType,
                'status' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ];
        }
        Notification::insert($postedData);
        // foreach ($registeredTokens as $k => $v) {
        //     echo $this->sendNotifMultiple($request->title, $request->message, $v, $request->userAppType);
        // }

        $this->sendNotifMultiple($request->title, $request->message, $users, $request->userAppType);

        // dd("success");

        return back()->with('message', 'Notification sent successfully');

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

    // public function sendNotif($title, $message, $token, $payload = null)
    // {
    //     $url = "https://fcm.googleapis.com/fcm/send";

    //     $serverKey = 'AAAA10hB_8I:APA91bHVSnAJjacznL6i3p9dWnKvJeceYJlTbwt_rvyq6Nx8tOPsMlxtYPqHzAJRAazC5JJof9PZHaw_uo1qbNkKK4YgJLKN_39ozcIlbCpt3YQ36Y5rT6ftegC0nnEiOZ-dYsYqFWcV';
    //     $body = $message;
    //     $notification = ['title' => $title, 'body' => $body, 'sound' => 'default', 'badge' => '1'];

    //     $apns = [
    //         'payload' => [
    //             'aps' => [
    //                 'sound' => 'default',
    //                 'badge' => 1,
    //                 'content-available' => 1
    //             ]
    //         ]
    //     ];

    //     $arrayToSend = [
    //         'to' => $token,
    //         'apns' => $apns,
    //         'data' => ['notification_forground' => 'true', 'extra' => ['hotDealId' => $payload]],
    //         'notification' => $notification,
    //         'priority' => 'high'
    //     ];

    //     $json = json_encode($arrayToSend);
    //     $headers = [];
    //     $headers[] = 'Content-Type: application/json';
    //     $headers[] = 'Authorization: key=' . $serverKey;
    //     $ch = curl_init();


    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_POST, true);
    //     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

    //     $response = curl_exec($ch);

    //     if ($response === false) {
    //         die('FCM Send Error: ' . curl_error($ch));
    //     }
    //     curl_close($ch);
    //     return $response;
    // }

    public function sendNotif($title, $message, $user, $payload = null)
    {
        $user->notify(new SNTCNotification($title, $message, $payload));
    }
    // public function sendNotifMultiple($title, $message, $tokens , $payload = null)
    // {


    //     $url = 'https://fcm.googleapis.com/v1/projects/sntc-73467/messages:send';

    //         $notif = [
    //             "message" => [
    //                 "token" => "cGzzg20-RwOJ-1HnD5sfaO:APA91bHASbUPacqon9gT3G93vqa10TPBeky599w8lSw5D5KYUT1SXmFq_2iEpArVaMm4eB4-PP-Fs-1hE82JEW3y1k53yhMRPkmZSLTGMG1B-XzFUtyvdiJwA8JDSZ1P2Y2JFRfwGXcd",
    //                 "data" => [
    //                     "body" => "Body of Your Notification in data",
    //                     "title" => "Title of Your Notification in data",
    //                     "key_1" => "Value for key_1",
    //                     "key_2" => "Value for key_2"
    //                 ]
    //             ]
    //         ];


    //         $ch = curl_init();

    //         curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/sntc-73467/messages:send');
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //         curl_setopt($ch, CURLOPT_POST, 1);
    //         curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n\"message\": {\n\"token\": \"cGzzg20-RwOJ-1HnD5sfaO:APA91bHASbUPacqon9gT3G93vqa10TPBeky599w8lSw5D5KYUT1SXmFq_2iEpArVaMm4eB4-PP-Fs-1hE82JEW3y1k53yhMRPkmZSLTGMG1B-XzFUtyvdiJwA8JDSZ1P2Y2JFRfwGXcd\",\n\"data\": {\n\"body\": \"Body of Your Notification in data\",\n\"title\": \"Title of Your Notification in data\",\n\"key_1\": \"Value for key_1\",\n\"key_2\": \"Value for key_2\"\n}\n}\n}");

    //         $headers = array();
    //         $headers[] = 'Authorization: Bearer <Access token>';
    //         $headers[] = 'Content-Type: application/json';
    //         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    //         $result = curl_exec($ch);
    //         print_r($result);
    //         exit();

    //         if (curl_errno($ch)) {
    //             echo 'Error:' . curl_error($ch);
    //         }
    //         curl_close($ch);




    //         return false;


    //     $url = "https://fcm.googleapis.com/fcm/send";

    //     $serverKey = 'AAAA10hB_8I:APA91bHVSnAJjacznL6i3p9dWnKvJeceYJlTbwt_rvyq6Nx8tOPsMlxtYPqHzAJRAazC5JJof9PZHaw_uo1qbNkKK4YgJLKN_39ozcIlbCpt3YQ36Y5rT6ftegC0nnEiOZ-dYsYqFWcV';
    //     $body = $message;
    //     $notification = ['title' => $title, 'body' => $body, 'sound' => 'default', 'badge' => '1' ];

    //     $apns = ['payload' => [
    //                 'aps' => [
    //                     'sound' => 'default' ,
    //                     'badge' => 1 ,
    //                     'content-available' => 1
    //                 ]
    //                 ]
    //             ];

    //     $arrayToSend = [
    //                 'registration_ids' => $tokens,
    //                 'apns' => $apns,
    //                 'data' => [ 'notification_forground' => 'true','extra' => [ 'hotDealId' => $payload]],
    //                 'notification' => $notification,
    //                 'priority'=>'high'
    //             ];

    //     $json = json_encode($arrayToSend);
    //     $headers = [];
    //     $headers[] = 'Content-Type: application/json';
    //     $headers[] = 'Authorization: key='. $serverKey;
    //     $ch = curl_init();


    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_POST, true);
    //     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

    //     $response = curl_exec($ch);

    //     if ($response === false) {
    //         die('FCM Send Error: ' . curl_error($ch));
    //     }
    //     curl_close($ch);
    //     return $response;
    // }

    public function sendNotifMultiple($title, $message, $users, $payload = null)
    {
        foreach ($users as $user) {
            try {
                $user->notify(new SNTCNotification($title, $message, $payload));
            } catch (\Throwable $th) {
                //throw $th;
                Log::error("Error in sending '$title' notification to User Id: {$user->id}" . $th->getMessage());
            }
        }
    }

    public function getUserNotifications($user_id = null)
    {
        if (is_null($user_id)) {
            return response()->json(['status' => false, 'message' => 'Required Parameters Missing !'], 200);
        }

        $notifications = Notification::where('user_id', $user_id)->latest()->take(50)->get();

        if ($notifications->count() <= 0) {
            return response()->json(['status' => false, 'message' => 'User not found !'], 200);
        }
        return response()->json(['status' => true, 'data' => $notifications->toArray()], 200);
    }

    public function hotDealIndex()
    {
        $todayDate = Carbon::now()->format('Y-m-d');
        $hotDeal = HotDealNotification::with(['getUSDDefaultMaster', 'getRiceQuality'])->orderBy('id', 'desc')->get();
        $packing = USD_defaultmaster::get();
        $quality = QualityMaster::get();

        return view('hotdeals.index', compact('packing', 'quality', 'todayDate', 'hotDeal'));
    }

    public function hotDealPushNotification(Request $request)
    {
        $attachmentOneName = '';
        $attachmentTwoName = '';

        if ($request->hasFile('attachment1')) {
            $image = $request->file('attachment1');
            $attachmentOneName = time() . '' . rand('1000', '9999') . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/notifications/');
            $image->move($destinationPath, $attachmentOneName);
        }

        if ($request->hasFile('attachment2')) {
            $image = $request->file('attachment2');
            $attachmentTwoName = time() . '' . rand('1000', '9999') . '.' . $image->getClientOriginalExtension();
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
            'length' => ($request->has('Length')) ? $request->Length : 0,
            'purity' => ($request->has('Purity')) ? $request->Purity : 0,
            'moisture' => ($request->has('Moisture')) ? $request->Moisture : 0,
            'broken' => ($request->has('Broken')) ? $request->Broken : 0,
            'kett' => ($request->has('Kett')) ? $request->Kett : 0,
            'dd' => ($request->has('DDs')) ? $request->DDs : 0
        ]);

        $users = User::select('id', 'user_token')->where('role', 5)->where('country', '!=', null)->where('country', '!=', 'india')->get();

        foreach ($users as $key => $user) {
            if ($user != null) {
                if ($user->user_token != null) {
                    // echo ($this->sendNotif($request->title, $request->message, $user->user_token, $hotdeal->id));//by parth

                    $this->sendNotif($request->title, $request->message, $user, $hotdeal->id);
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
    public function updateHotDealStatus($statusToken, $hotDealNotifId)
    {
        HotDealNotification::where('id', $hotDealNotifId)->update(['status' => $statusToken]);
        return back();
    }
}
