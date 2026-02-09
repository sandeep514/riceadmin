<?php

namespace App\Http\Controllers;

use App\Courier;
use App\LivePrice;
use App\MillStatus;
use App\Packing;
use App\PackingType;
use App\Quality;
use App\Repositories\CourierRepository;
use App\Sample;
use App\User;
use App\ChartInterval;
use App\Port;
use App\PortImages;
use App\Role;
use App\Gallery;
use App\Contact;
use App\PaddyPrice;
use App\RiceName;
use App\RiceType;
use App\RiceForm;
use App\Order;
use App\BuyQuery;
use App\Plan;
use App\SubPlan;
use App\Message;
use App\TrialPeriod;
use App\Version;
use App\OceanFreight;
use App\BagVendors;
use App\Helpers\StatusChat;
use App\USD_prices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\FreeTrialMonths;
use App\QualityMaster;
use App\USD_defaultmaster;
use App\Defaultvalue;
use App\Vendorcategory;
use App\Bid;
use App\USDPlan;
use App\HotDealAccept;
use App\HotDealNotification;
use App\Http\Controllers\MailController;
use Illuminate\Support\Str;
use App\Notification;
use App\Brand;
use App\WandModel;
use App\WandTypeModel;
use App\SellerPackingINR;
use App\RiceFormMilestone3;
use App\SellQueriesINR;
use App\TradeQueriesINR;
use App\TradeStatusMessages;
use App\Buyerpackinginr;
use App\BuyQueriesINR;
use App\TradeLike;
use App\TradeIntrested;
use Mail;
use Auth;
use App\NewsRunner;
use App\TradeCurrentStatus;
use App\WebBusinessDetails;
use App\WebPersonalDetails;
use App\WebUserAttachment;
use App\WebPlanModel;
use App\WebPlanKeysModel;
use App\WebUserSubscriptionModel;
use App\VendorUserMap;
use App\ServiceProviderUserMap;
use Razorpay\Api\Api;
use Exception;
use Illuminate\Support\Facades\File;

class PortalApiController extends Controller
{

    public function uploadAttachments($file, $destination, array $requiredExtentionValidation)
    {
        // $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $fileextension = $file->getClientOriginalExtension();

        if (in_array($fileextension, $requiredExtentionValidation)) {

            // $destinationPath = 'uploads/gallery';
            $file->move($destination, $filename);
            return $filename;
            // $gallery = Gallery::where('id', $request->id)->update(['attachment' => $galleryCounter . '_' . $filename]);
            return;
        } else {
            return back()->withErrors(['error' => "File type not allowed ...! only jpg , jpeg, png is allowed."]);
        }
    }

    public function loginUser(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'mobile' => 'required|regex:/^[0-9]{10}$/'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }
        if ($request->has('mobile') ) {
            $mobile = $request->mobile;
            $user = User::where(['mobile' => $mobile, 'userType' => 2])->first();
            $Newotp = rand(1000, 9999);

            if (!$user) {
                $user = User::create(['mobile' => $mobile, 'otp' => $Newotp, 'userType' => 2,"user_from" => "web"]);
            } else {
                $user->update(['otp' => $Newotp]);
            }

            $hasBasicDetails = false;


            $message = "SNTC rice sourcing OTP is $Newotp. Do not share this with anyone. - SNTC AGRO TECHNOLOGY";

            $url = "http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto="
                  . $mobile
                  . "&message=" . urlencode($message)
                  . "&PEID=1701172916686910712&templateid=1707176544745633588";
            if ($user) {
                file_get_contents($url);
                return response()->json(['status' => true, 'message' => 'OTP sent successfully','data' => $user], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'Wrong user credentials'], 401);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Required fields are missing'], 401);
        }
    }

    public function verifyOTPAndLogin(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'mobile' => 'required|regex:/^[0-9]{10}$/',
            'otp' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }
        if ($request->has('mobile') ) {
            $mobile = $request->mobile;
            $otp = $request->otp;
            $user = User::where(['mobile' => $mobile,'otp' => $otp]);

            $hasBasicDetails = false;

            if ($user->first()) {
                $data = $user->with(['getWebPersonalDetails', 'getWebBusinessDetails' => function($q){
                    return $q->with(['getCategoryDetails:id,category']);
                }, 'getWebUserAttachment','getWebUserSubscription' => function($q){
                    return $q->whereDate('period_end' , '>=' , Carbon::now()->format('Y-m-d'));
                }])->first();

                $hasActivePlan = false;
                if( $data->getWebUserSubscription ){
                    $hasActivePlan = true;
                }
                if ($data->getWebPersonalDetails != null || $data->getWebBusinessDetails != null || $data->getWebUserAttachment != null) {
                    $hasBasicDetails = true;
                }

                $checkIfTrailDone = WebUserSubscriptionModel::where('user_id' , $user->first()->id)->where('subscription_type' , 'trial')->first();
                
                $hasTrialDone = false;
                if( $checkIfTrailDone ){
                    $hasTrialDone = true;
                }

                return response()->json(['status' => true, 'message' => 'Success', 'hasBasicDetails' => $hasBasicDetails,'hasTrialDone' => $hasTrialDone,'hasActivePlan' => $hasActivePlan,'planDetails' => $data->getWebUserSubscription , 'data' => $data], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'Wrong user credentials'], 401);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Required fields are missing'], 401);
        }
    }

    public function saveUser(Request $request)
    {
        $mobile = $request['mobile'];
        $Newotp = rand(1000, 9999);
        $user = User::where(['mobile' => $mobile, 'userType' => 2,'user_from' => 'web'])->first();
        if (!$user) {
            $user = User::create(['mobile' => $mobile, 'otp' => $Newotp, 'userType' => 2,'user_from' => 'web']);
        } else {
            $user->update(['otp' => $Newotp]);
            // return response()->json(['status' => false , 'message' => 'User already available', 'isVerified' => ($user->is_INR_active == 1)? true: false ] , 401);
        }
        // file_get_contents('http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto=' . $mobile . '&message=Thank+you+for+registering+on+SNTC+Rice+Live+Pricing+App.+Your+OTP+Code+is+' . $Newotp . '.+SNTCAL&PEID=1701172916686910712&templateid=1707172924575773908');
        $message = "SNTC rice sourcing OTP is $Newotp. Do not share this with anyone. - SNTC AGRO TECHNOLOGY";

        $url = "http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto="
              . $mobile
              . "&message=" . urlencode($message)
              . "&PEID=1701172916686910712&templateid=1707176544745633588";
        file_get_contents($url);
        return response()->json(['status' => true, 'message' => 'OTP sent successfully on ' . $mobile, 'data' => ['user_id' => $user->id], 'isVerified' => ($user->is_INR_active == 1) ? true : false], 200);
    }

    public function verifyOTP(Request $request)
    {
        if ($request->has('user_id') && $request->has('otp') && $request->user_id != '' && $request->otp != '') {
            $user_id = $request->user_id;
            $otp = $request->otp;
            $user = User::where(['id' => $user_id]);

            $isOTPSame = $user->where(['otp' => $otp])->first();
            $hasBasicDetails = false;

            if ($isOTPSame) {
                $user->update(['is_INR_active' => 1]);

                $data = $user->with(['getWebPersonalDetails', 'getWebBusinessDetails' => function($q){
                    return $q->with(['getCategoryDetails:id,category']);
                }, 'getWebUserAttachment','getWebUserSubscription' => function($q){
                    return $q->whereDate('period_end' , '>=' , Carbon::now()->format('Y-m-d'));
                }])->first();
                
                $hasActivePlan = false;
                if( $data->getWebUserSubscription ){
                    $hasActivePlan = true;
                }
                
                // return response()->json(['status' => true, 'message' => 'OTP verified successfully', 'hasBasicDetails' => $data,'hasActivePlan' => false], 200);
                
                if ($data->getWebPersonalDetails != null || $data->getWebBusinessDetails != null || $data->getWebUserAttachment != null) {
                    $hasBasicDetails = true;
                }

                $checkIfTrailDone = WebUserSubscriptionModel::where('user_id' , $isOTPSame->id)->where('subscription_type' , 'trial')->first();
                
                $hasTrialDone = false;
                if( $checkIfTrailDone ){
                    $hasTrialDone = true;
                }


                return response()->json(['status' => true, 'message' => 'OTP verified successfullyy', 'hasBasicDetails' => $hasBasicDetails,'hasActivePlan' => $hasActivePlan,'hasTrialDone' => $hasTrialDone,'planDetails' => $data->getWebUserSubscription , 'data' => $data], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'Wrong OTP'], 401);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Required fields are missing'], 401);
        }
    }
    
    public function resendOTP(Request $request)
    {
        if ($request->has('user_id')) {
            $otp = round(1000, 9999);
            $user_id = $request->user_id;

            $user = User::where(['id' => $user_id]);
            if ($user->first()) {
                $user->update(['otp' => $otp]);
                $mobile = ($user->first()->mobile);

                $message = "SNTC rice sourcing OTP is $otp. Do not share this with anyone. - SNTC AGRO TECHNOLOGY";

                $url = "http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto="
                      . $mobile
                      . "&message=" . urlencode($message)
                      . "&PEID=1701172916686910712&templateid=1707176544745633588";
                file_get_contents($url);

                // file_get_contents('http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto=' . $mobile . '&message=Thank+you+for+registering+on+SNTC+Rice+Live+Pricing+App.+Your+OTP+Code+is+' . $otp . '.+SNTCAL&PEID=1701172916686910712&templateid=1707172924575773908');

                return response()->json(['status' => true, 'message' =>  'OTP send successfully'], 200);
            } else {
                return response()->json(['status' => false, 'message' => $mobile . ' not available in records'], 401);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Required fields are missing'], 401);
            //error : required fields are missing
        }
    }

    public function updateUserDetails(Request $request)
    {
        $user_id = $request->user_id;

        $personalDetails = [];
        $businessDetails = [];
        $userEmailForMail = '';
        if ($request->has('personal_details')) {
            $personalDetails = $request->personal_details;

            $name = $personalDetails['firstname'].' '.$personalDetails['lastname'];
            $email = $personalDetails['email'];
            $userEmailForMail = $personalDetails['email'];

            User::where('id' , $user_id)->update(['name' => $name,'email' => $email]);

            if (array_key_exists('avatar', $personalDetails)) {

                // Define real filesystem path
                $basePath = public_path('webPortal/' . $user_id . '/attachments/avatar/');

                // Recursively create directories if not exist
                if (!File::isDirectory($basePath)) {
                    File::makeDirectory($basePath, 0755, true, true);
                }

                // Upload file to this directory
                $file = $this->uploadAttachments($personalDetails['avatar'], $basePath, ['jpeg', 'jpg', 'png']);

                // Save only the file name or relative path
                $personalDetails['avatar'] = $file;
            }

            $personalDetails['user_id'] = $request['user_id'];
            if( $request->has('role') ){
                User::where('id' ,$user_id)->update(['role' => $request->role]);
            }
            WebPersonalDetails::updateOrCreate(['user_id' => $user_id], $personalDetails);
        }

        if ($request->has('business_details')) {
            $businessDetails = $request->business_details;
            $businessDetails['user_id'] = $request['user_id'];
            WebBusinessDetails::updateOrCreate(['user_id' => $user_id], $businessDetails);

            if( $request->has('role') && $request->role == 11 ) {
                $vendorDetails = [
                    'user_id' => $user_id,
                    'type' => $request['vendorDetails']['type']??'--',
                    'key' => $request['vendorDetails']['key']??'--',
                    'value' => $request['vendorDetails']['value']??'--',
                    'remarks' => $request['vendorDetails']['remarks']??'--',
                    'status' => 1
                ];
                VendorUserMap::create($vendorDetails);
            }

            if( $request->has('role') && $request->role == 12 ) { 
               $serviceProviderDetails = [
                    'user_id' => $user_id,
                    'type' => $request['serviceProviderDetails']['type']??'--',
                    'key' => $request['serviceProviderDetails']['key']??'--',
                    'value' => $request['serviceProviderDetails']['value']??'--',
                    'remarks' => $request['serviceProviderDetails']['remarks']??'--',
                    'status' => 1
                ];
                ServiceProviderUserMap::create($serviceProviderDetails);
            }
        }

        if ($request->has('documents.pan_file')) {
            $dirname = 'webPortal/' . $user_id . '/attachments/pan/';
            if (!$dirname) {
                mkdir(asset($dirname, 0755));
            }
            $file = $this->uploadAttachments($request->file('documents.pan_file'), $dirname, ['jpeg', 'jpg', 'png', 'pdf']);
            WebUserAttachment::updateOrCreate(['user_id' => $user_id], ['panCard' => $file]);
        }

        if ($request->has('documents.farmer_file')) {
            $dirname = 'webPortal/' . $user_id . '/attachments/farmer_file/';
            if (!$dirname) {
                mkdir(asset($dirname, 0755));
            }
            $file = $this->uploadAttachments($request->file('documents.farmer_file'), $dirname, ['jpeg', 'jpg', 'png', 'pdf']);
            WebUserAttachment::updateOrCreate(['user_id' => $user_id], ['farmer_file' => $file]);
        }

        if ($request->has('documents.gst_file')) {
            $dirname = 'webPortal/' . $user_id . '/attachments/gst/';
            if (!$dirname) {
                mkdir(asset($dirname, 0755));
            }
            $file = $this->uploadAttachments($request->file('documents.gst_file'), $dirname, ['jpeg', 'jpg', 'png', 'pdf']);
            WebUserAttachment::updateOrCreate(['user_id' => $user_id], ['gstCard' => $file]);
        }

        if ($request->has('documents.fssai_file')) {
            $dirname = 'webPortal/' . $user_id . '/attachments/fssai/';
            if (!$dirname) {
                mkdir(asset($dirname, 0755));
            }
            $file = $this->uploadAttachments($request->file('documents.fssai_file'), $dirname, ['jpeg', 'jpg', 'png', 'pdf']);
            WebUserAttachment::updateOrCreate(['user_id' => $user_id], ['fssaiCard' => $file]);
        }
        

        $mailTo = 'info@sntcgroup.com';
        $mailMessage = '';
        $subject = 'User update the profile';
        $mailFrom = 'info@sntcgroup.com';
        $mailFromName = 'SNTC Team - India';


        $data = ['userEmail' => $userEmailForMail];

        $respose = Mail::send('mail.userUpdateProfile', $data, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
            $message->to($mailTo, $mailMessage)->subject($subject);
            $message->from($mailFrom, $mailFromName);
        });


        return response()->json(['status' => true, 'message' => 'user details added successfully', 'data' => ['personalDetails' => $personalDetails, 'businessDetails' => $businessDetails]], 200);
    }

    public function getUserDetails($userId)
    {
        if ($userId != null) {
            $user = User::where('id', $userId)->where('userType', 2)->with(['getWebPersonalDetails', 'getWebBusinessDetails' => function($q){
                return $q->with(['cityRel:id,city_name' , 'stateRel:id,state_name', 'getCategoryDetails:id,category' , 'getBagVendorWeb:id,category']);
            }, 'getWebUserAttachment','getWebUserSubscription','role_rel'])->first()->toArray();

            // if( $user['role'] == 12 ){
            //     $user['get_web_business_details']['get_category_details'] =  $user['get_web_business_details']['get_bag_vendor_web'];
            // }
            unset($user['get_web_business_details']['get_bag_vendor_web']);

            return response()->json(['status' => true, 'message' => 'user details added successfully', 'data' => $user, 'prefix' => [
                'avatar' => 'webPortal/' . $userId . '/attachments/avatar',
                'gst' => 'webPortal/' . $userId . '/attachments/gst',
                'pan' => 'webPortal/' . $userId . '/attachments/pan',
                'fssai' => 'webPortal/' . $userId . '/attachments/fssai'
            ]], 200);


        } else {
            return response()->json(['status' => false, 'message' => 'required field is missing', 'data' => []], 401);
        }
    }

    public function deleteUser($userId)
    {
        if ($userId != null) {
            $user = User::where('id', $userId)->where('userType', 2)->delete();
            return response()->json(['status' => true, 'message' => 'user deleted successfully', 'data' => $user], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'userid required', 'data' => []], 401);
        }
    }

    public function getPlans()
    {

        $webKeys = WebPlanKeysModel::select(["id","key","status"])->where(['status'  =>  1])->get()->pluck('key','id');
        $plans = WebPlanModel::select(["id","title","short_description","description","status"])->where('title' ,'!=' , '')->with(['getPlanKeyMap:key_id,plan_id'])->where(['status' => 1])->get()->map(function($q){
            return ['plan' => ['id' => $q->id,'title' => $q->title,'short_description' => $q->short_description,'description' => $q->description,'status' => $q->status], 'availableKeys' => $q->getPlanKeyMap->pluck('key_id')];
        })->toArray();
        return response()->json(['status' => true, 'message' => 'Web Plans', 'data' => ['plans' => $plans , 'webKeys' => $webKeys]], 200);
    }

    public function webUserSubscription(Request $request)
    {
        $validate = $request->validate([
            'user_id' => 'required',
            'plan_id' => 'required',
            'subscription_type' => 'required',
        ]);
        $data = [
            'user_id' => $request->user_id,
            'plan_id' => $request->plan_id,
            'subscription_type' => $request->subscription_type,
            'period_start' =>  Carbon::now()->format('Y-m-d'),
            'period_end' => Carbon::now()->addDays(7)->format('Y-m-d')
        ];
        WebUserSubscriptionModel::create($data);
        return response()->json(['status' => true, 'message' => 'User subscription added successfully', 'data' => $data], 200);

    }

    public function getWebPlans()
    {
        $role = Role::select(["id","role_name"])->where('type' , 'web')->get();
        return response()->json(['status' => true, 'message' => 'Role get successfully', 'data' => $role], 200);
    }

    // POST /api/portal/create-order
    public function webCreateOrder(Request $request) {

        $userId = $request->user_id;
        $planId = $request->plan_id;
        $amount = $request->amount;
        $currency = $request->currency ?? 'INR';
        $billingPeriod = $request->billing_period;

        $api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );

        $order = $api->order->create([
            'amount' => $amount * 100, // amount in paise
            'currency' => $currency,
            'receipt' => 'receipt_' . $userId . '_' . time(),
            'notes' => [
                'user_id' => $userId,
                'plan_id' => $planId,
                'billing_period' => $billingPeriod,
            ],
        ]);

        return response()->json([
            'status' => true,
            'order_id' => $order['id'],
            'amount' => $amount,
            'currency' => $currency,
        ]);
    }


    public function webVerifyPayment(Request $request)
    {
        $razorpayPaymentId = $request->razorpay_payment_id;
        $razorpayOrderId   = $request->razorpay_order_id;
        $razorpaySignature = $request->razorpay_signature;
        $userId            = $request->user_id;
        $planId            = $request->plan_id;

        // Initialize Razorpay API with correct credentials
        $api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );

        // Prepare attributes for verification
        $attributes = [
            'razorpay_order_id'   => $razorpayOrderId,
            'razorpay_payment_id' => $razorpayPaymentId,
            'razorpay_signature'  => $razorpaySignature
        ];

        try {
            // Verify payment signature
            $api->utility->verifyPaymentSignature($attributes);
            $addedDays  = 7;
            if( $request->subscription_type =='trial' ){
                $addedDays = 7;
            }elseif( $request->subscription_type =='monthly' ){
                $addedDays = 30;
            }elseif( $request->subscription_type =='half_yearly' ){
                $addedDays = 183;
            }elseif( $request->subscription_type =='yearly' ){
                $addedDays = 365;
            }

            // ✅ Signature matched successfully — store the subscription/payment
            $subscription = WebUserSubscriptionModel::create([
                'user_id'      => $userId,
                'plan_id'      => $planId,
                'payment_id'   => $razorpayPaymentId,
                'order_id'     => $razorpayOrderId,
                'status'       => 'active',
                'period_start' => now(),
                'period_end'   => now()->addDays($addedDays) ,
                'subscription_type' => $request->subscription_type,
                'status' => 1
            ]);

            $userDetails = User::where(['id' => $userId])->first();
            if( $request->subscription_type =='trial' ){

                User::where(['id' => $userId])->update(['has_validation' => "Your profile is under review. We will notify you once approved."]);

                // send trial mail
                $mailTo = $userDetails->email;
                $mailMessage = '';
                $subject = 'Your SNTC 7-Day Free Trial is Now Active.';
                $mailFrom = 'info@sntcgroup.com';
                $mailFromName = 'SNTC Team - India';


                $data = ['userName' => $userDetails->name , 'userEmail' => $userDetails->email];

                $respose = Mail::send('mail.sendTrailMailToUser', $data, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
                    $message->to($mailTo, $mailMessage)->subject($subject);
                    $message->from($mailFrom, $mailFromName);
                });

                $subject = 'New User Registration-Webversion';
                $mailTo = 'info@sntcgroup.com';
                $respose = Mail::send('mail.newUserAdded', $data, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
                    $message->to($mailTo, $mailMessage)->subject($subject);
                    $message->from($mailFrom, $mailFromName);
                });
                
            }else{
                
                $mailTo = $userDetails->email;
                $mailMessage = '';
                $subject = 'Subscription Activated – Welcome to SNTC';
                $mailFrom = 'info@sntcgroup.com';
                $mailFromName = 'SNTC Team - India';
                
                $data = ['userName' => $userDetails->name , 'userEmail' => $userDetails->email];
                $respose = Mail::send('mail.AccrountActiveWebMail', $data, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
                    $message->to($mailTo, $mailMessage)->subject($subject);
                    $message->from($mailFrom, $mailFromName);
                });
            }

            

            return response()->json([
                'status'  => true,
                'message' => '✅ Payment verified and subscription activated.',
                'data'    => $subscription
            ]);

        } catch (Exception $e) {
            // ❌ Signature verification failed
            return response()->json([
                'status'  => false,
                'message' => '❌ Payment verification failed.',
                'error'   => $e->getMessage(),
            ], 400);
        }
    }

    public function getLatestUpdatedCount()
    {
        $todayDate = Carbon::now()->format('Y-m-d');
        $livePricesCount = LivePrice::whereDate('created_at' ,  $todayDate)->count();
        $paddyMandiCount = PaddyPrice::whereDate('created_at' , $todayDate)->count();
        $tradeCount = TradeQueriesINR::whereDate('created_at' , $todayDate)->count();

        return response()->json([
                'status'  => true,
                'message' => 'count get successfully',
                'data' => ['liveCount' => $livePricesCount , 'paddyCount' => $paddyMandiCount , 'tradeCount' => $tradeCount ] 
            ], 200);
    }

}

