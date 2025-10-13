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
                $user = User::create(['mobile' => $mobile, 'otp' => $Newotp, 'userType' => 2]);
            } else {
                $user->update(['otp' => $Newotp]);
            }

            $hasBasicDetails = false;

            if ($user) {
                file_get_contents('http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto=' . $mobile . '&message=Thank+you+for+registering+on+SNTC+Rice+Live+Pricing+App.+Your+OTP+Code+is+' . $Newotp . '.+SNTCAL&PEID=1701172916686910712&templateid=1707172924575773908');

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
                $data = $user->with(['getWebPersonalDetails', 'getWebBusinessDetails', 'getWebUserAttachment','getWebUserSubscription' => function($q){
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
        $user = User::where(['mobile' => $mobile, 'userType' => 2])->first();
        if (!$user) {
            $user = User::create(['mobile' => $mobile, 'otp' => $Newotp, 'userType' => 2]);
        } else {
            $user->update(['otp' => $Newotp]);
            // return response()->json(['status' => false , 'message' => 'User already available', 'isVerified' => ($user->is_INR_active == 1)? true: false ] , 401);
        }
        file_get_contents('http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto=' . $mobile . '&message=Thank+you+for+registering+on+SNTC+Rice+Live+Pricing+App.+Your+OTP+Code+is+' . $Newotp . '.+SNTCAL&PEID=1701172916686910712&templateid=1707172924575773908');

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
                $data = $user->with(['getWebPersonalDetails', 'getWebBusinessDetails', 'getWebUserAttachment','getWebUserSubscription' => function($q){
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
                file_get_contents('http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto=' . $mobile . '&message=Thank+you+for+registering+on+SNTC+Rice+Live+Pricing+App.+Your+OTP+Code+is+' . $otp . '.+SNTCAL&PEID=1701172916686910712&templateid=1707172924575773908');

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
        if ($request->has('personal_details')) {
            $personalDetails = $request->personal_details;

            $name = $personalDetails['firstname'].' '.$personalDetails['lastname'];
            $email = $personalDetails['email'];
            User::where('id' , $user_id)->update(['name' => $name,'email' => $email]);

            if (array_key_exists('avatar', $personalDetails)) {
                $dirname = 'webPortal/' . $user_id . '/attachments/avatar/';
                if (!$dirname) {
                    mkdir(asset($dirname, 0755));
                }
                $file = $this->uploadAttachments($personalDetails['avatar'], $dirname, ['jpeg', 'jpg', 'png']);
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

            if( isset($request['business_details']['selected_category']) && $request['business_details']['selected_category'] =='Vendor' ) {
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
            
            if( isset($request['business_details']['selected_category']) && $request['business_details']['selected_category'] =='Service Provider' ) { 
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
            WebUserAttachment::updateOrCreate(['user_id' => $user_id], ['fssai' => $file]);
        }
        
        

        return response()->json(['status' => true, 'message' => 'user details added successfully', 'data' => ['personalDetails' => $personalDetails, 'businessDetails' => $businessDetails]], 200);
    }

    public function getUserDetails($userId)
    {
        if ($userId != null) {
            $user = User::where('id', $userId)->where('userType', 2)->with(['getWebPersonalDetails', 'getWebBusinessDetails', 'getWebUserAttachment'])->first();
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



}

