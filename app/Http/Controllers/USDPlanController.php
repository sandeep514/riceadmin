<?php

namespace App\Http\Controllers;

use App\DataTables\GalleryReportDatatable;
use Illuminate\Http\Request;
use App\Coupon;
use App\USDPlan;
use App\Bid;
use App\User;

class USDPlanController extends Controller
{
	public function index()
	{
		$coupon = Coupon::all();
		return View('coupon.index' , compact('coupon'));
	}

	public function saveUSDPlan(Request $request)
	{
		$data = [
			'coupon_name' => $request->couponName,
			'coupon_feature' => $request->couponFeature ,
			'coupon_description' => $request->description ,
			'coupon_percentage' => $request->discountPercentage ,
			'coupon_expiry' => $request->expiryDate ,
			'status' => 1,
			'maxDiscount' => $request->maxDiscount
		];
		Coupon::create($data);
		return back();
	}

	public function ChangeStatus($id)
	{
		$statusChange = Coupon::where('id' , $id)->get();
		if( $statusChange->count() > 0 ) {
			$selectedPlan = $statusChange[0];

			if( $selectedPlan->status == 0 ){
				Coupon::where('id' , $id)->update(['status' => 1]);
			}else{
				Coupon::where('id' , $id)->update(['status' => 0]);
			}
		}
		return back();
	}

	public function Planindex()
	{
		$usdPlan = USDPlan::all();
		return View('USDPlan.index' , compact('usdPlan'));
	}

	public function PlanChangeStatus($id)
	{
		$statusChange = USDPlan::where('id' , $id)->get();
		if( $statusChange->count() > 0 ) {
			$selectedPlan = $statusChange[0];
			
			if( $selectedPlan->status == 0 ){
				USDPlan::where('id' , $id)->update(['status' => 1]);
			}else{
				USDPlan::where('id' , $id)->update(['status' => 0]);
			}
		}
		return back();
	}

	public function PlansaveUSDPlan(Request $request)
	{
		$data = [
	        'plan_name' 	=> $request->planname,
	        'plan_desc' 	=> $request->plandesc,
	        'valid_months' 	=> $request->validmonth,
	        'actual_price'=> $request->actual_price,
			'discounted_prie'=> $request->discounted_prie,
	        'status' 		=> 1
	    ];
		USDPlan::create($data);
		return back();
	}
	public function termCondition()
	{
		return View('terms.create');	
	}
	public function saveTermCondition(Request $request)
	{
		dd($request->all());
	}
	public function sendSellerConfirmMessage(Request $request)
	{
		$bid = Bid::where([ 'query_id' => $request->bid_id ])->with('buyerQuery')->first();
		$user = $bid->buyerQuery['user'];

		$getUserDetails = User::where(['id' => $user])->get();
		$getUserToken = null; 

		Bid::where(['query_id' => $request->bid_id ])->update(['counter_amount' => $request->price]);
		if( $getUserDetails->count() > 0 ){
			$getUserToken = $getUserDetails[0]['api_token'];
		}

		$message = 'SNTC send you a counter offer of $1800, Would you like to Accept.';
		if( $getUserToken != null ){
			$this->sendNotif( 'Counter Amount', $message , $getUserToken );
		}
		return back();

	}
	public function sendNotif($title, $message, $token, $buyerQuery)
        {
            $token = "c_kAXj3VQO6vUapdES8jMo:APA91bEe_kp_c-B0YcXCFWnwNd-9VzQ0BzKXOJEoSoKP5hxX3qKxGPSugmk6N_VIdurkWVQwSx26t5AlDSggaUWpvLGgPa-dgMSg-a9soUA67e6YipBs84pXQVoM5tzOh_t8W-5Hefbn";

            $url = "https://fcm.googleapis.com/fcm/send";
            $serverKey = 'AAAA10hB_8I:APA91bHVSnAJjacznL6i3p9dWnKvJeceYJlTbwt_rvyq6Nx8tOPsMlxtYPqHzAJRAazC5JJof9PZHaw_uo1qbNkKK4YgJLKN_39ozcIlbCpt3YQ36Y5rT6ftegC0nnEiOZ-dYsYqFWcV';
            $body = $message;
            $notification = ['title' => $title, 'body' => $body, 'sound' => 'default', 'badge' => '1','data' => 'here'];
            
            $apns = ['payload' => 
                        [
                            'aps' => [
                                'sound' => 'default' ,
                                'badge' => 1 ,
                                'content-available' => 1,
                                'data' => ['messageFrom' => "here"],
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
                        'priority'=>'high',
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
}