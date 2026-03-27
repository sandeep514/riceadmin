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
use App\WebAccess;
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
            // ✅ Ensure we're finding/creating the correct user with proper scoping
            $user = User::where(['mobile' => $mobile, 'userType' => 2, 'user_from' => 'web'])->first();
            $Newotp = rand(1000, 9999);

            if (!$user) {
                // ✅ Create new user if not found
                $user = User::create(['mobile' => $mobile, 'otp' => $Newotp, 'userType' => 2, 'user_from' => 'web']);
            } else {
                // ✅ Update OTP for existing user (only updates the specific user found)
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
        if ($request->has('mobile')) {
            $mobile = $request->mobile;
            $otp = $request->otp;
            $user = User::where(['mobile' => $mobile, 'otp' => $otp, 'userType' => 2])->first();

            if ($user) {
                // ✅ Create Laravel session using 'web' guard (sets httpOnly cookie automatically)
                auth('web')->login($user);
                
                // ✅ Ensure session is saved so Set-Cookie header is sent
                $request->session()->save();
                
                // ✅ Reload user with relationships using the user ID to ensure we get the correct user
                $data = User::where('id', $user->id)->where('userType', 2)->with(['getWebPersonalDetails', 'getWebBusinessDetails' => function($q){
                    return $q->with(['getCategoryDetails:id,category']);
                }, 'getWebUserAttachment','getWebUserSubscription' => function($q){
                    return $q->whereDate('period_end' , '>=' , Carbon::now()->format('Y-m-d'));
                }, 'role_rel'])->first();
                
                // Check if user data was found
                if (!$data) {
                    return response()->json(['status' => false, 'message' => 'User not found'], 404);
                }

                $hasActivePlan = false;
                if($data->getWebUserSubscription){
                    $hasActivePlan = true;
                }
                
                $hasBasicDetails = false;
                if ($data->getWebPersonalDetails != null || $data->getWebBusinessDetails != null || $data->getWebUserAttachment != null) {
                    $hasBasicDetails = true;
                }

                $checkIfTrailDone = WebUserSubscriptionModel::where('user_id', $user->id)->where('subscription_type', 'trial')->first();
                
                $hasTrialDone = false;
                if($checkIfTrailDone){
                    $hasTrialDone = true;
                }

                // ✅ Return response with session cookie
                return response()->json([
                    'status' => true, 
                    'message' => 'Success', 
                    'hasBasicDetails' => $hasBasicDetails,
                    'hasTrialDone' => $hasTrialDone,
                    'hasActivePlan' => $hasActivePlan,
                    'planDetails' => $data->getWebUserSubscription, 
                    'data' => $data
                ], 200);
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
            $user = User::where(['id' => $user_id, 'otp' => $otp, 'userType' => 2])->first();

            if ($user) {
                $user->update(['is_INR_active' => 1]);
                
                // ✅ Create session after OTP verification using 'web' guard
                auth('web')->login($user);
                
                // ✅ Ensure session is saved so Set-Cookie header is sent
                $request->session()->save();

                // ✅ Reload user with relationships using the user ID to ensure we get the correct user
                $data = User::where('id', $user->id)->where('userType', 2)->with(['getWebPersonalDetails', 'getWebBusinessDetails' => function($q){
                    return $q->with(['getCategoryDetails:id,category']);
                }, 'getWebUserAttachment','getWebUserSubscription' => function($q){
                    return $q->whereDate('period_end' , '>=' , Carbon::now()->format('Y-m-d'));
                }, 'role_rel'])->first();
                
                // Check if user data was found
                if (!$data) {
                    return response()->json(['status' => false, 'message' => 'User not found'], 404);
                }
                
                $hasActivePlan = false;
                if($data->getWebUserSubscription){
                    $hasActivePlan = true;
                }
                
                $hasBasicDetails = false;
                if ($data->getWebPersonalDetails != null || $data->getWebBusinessDetails != null || $data->getWebUserAttachment != null) {
                    $hasBasicDetails = true;
                }

                $checkIfTrailDone = WebUserSubscriptionModel::where('user_id', $user->id)->where('subscription_type', 'trial')->first();
                
                $hasTrialDone = false;
                if($checkIfTrailDone){
                    $hasTrialDone = true;
                }

                // ✅ Return response with session cookie
                return response()->json([
                    'status' => true, 
                    'message' => 'OTP verified successfully', 
                    'hasBasicDetails' => $hasBasicDetails,
                    'hasActivePlan' => $hasActivePlan,
                    'hasTrialDone' => $hasTrialDone,
                    'planDetails' => $data->getWebUserSubscription, 
                    'data' => $data
                ], 200);
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

            // Prevent duplicate web-user email during basic details save.
            if (!empty($email)) {
                $emailAlreadyUsed = User::where('email', $email)
                    ->where('user_from', 'web')
                    ->where('id', '!=', $user_id)
                    ->exists();

                if ($emailAlreadyUsed) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Email is already in use , please use different email or login if you already have account.'
                    ], 422);
                }
            }

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
            }, 'getWebUserAttachment','getWebUserSubscription.planRel','role_rel'])->first();

            if (!$user) {
                return response()->json(['status' => false, 'message' => 'User not found', 'data' => []], 404);
            }

            $user = $user->toArray();

            // if( $user['role'] == 12 ){
            //     $user['get_web_business_details']['get_category_details'] =  $user['get_web_business_details']['get_bag_vendor_web'];
            // }
            if (isset($user['get_web_business_details']['get_bag_vendor_web'])) {
                unset($user['get_web_business_details']['get_bag_vendor_web']);
            }

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
        $webKeys = WebPlanKeysModel::select(["id","key","status"])
            ->where(['status'  =>  1])
            ->get()
            ->pluck('key','id');
        $activeWebKeyIds = $webKeys->keys()->map(fn($id) => (int) $id)->toArray();

        $plans = WebPlanModel::select([
                "id","title","short_description","description","status",
                "monthly_price","quarterly_price","yearly_price",
                "monthly_discount_percentage","quarterly_discount_percentage","yearly_discount_percentage",
                "monthly_final_amount","quarterly_final_amount","yearly_final_amount"
            ])
            ->where('title' ,'!=' , '')
            ->with(['getPlanKeyMap:key_id,plan_id'])
            ->where(['status' => 1])
            ->get()
            ->map(function ($q) use ($activeWebKeyIds) {
                return [
                    'plan' => [
                        'id' => $q->id,
                        'title' => $q->title,
                        'short_description' => $q->short_description,
                        'description' => $q->description,
                        'status' => $q->status
                    ],
                    'pricing' => [
                        'monthly' => [
                            'price' => $q->monthly_price,
                            'discount_percentage' => $q->monthly_discount_percentage,
                            'final_amount' => $q->monthly_final_amount
                        ],
                        'quarterly' => [
                            'price' => $q->quarterly_price,
                            'discount_percentage' => $q->quarterly_discount_percentage,
                            'final_amount' => $q->quarterly_final_amount
                        ],
                        'yearly' => [
                            'price' => $q->yearly_price,
                            'discount_percentage' => $q->yearly_discount_percentage,
                            'final_amount' => $q->yearly_final_amount
                        ],
                    ],
                    'availableKeys' => $q->getPlanKeyMap
                        ->pluck('key_id')
                        ->map(fn($id) => (int) $id)
                        ->filter(fn($id) => in_array($id, $activeWebKeyIds))
                        ->values()
                ];
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

    /**
     * POST /api/portal/web/plans/by-role-category
     * Payload: { "role": 4, "category": 2 }
     */
    public function getWebPlansByRoleCategory(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'role' => 'required|integer|exists:roles,id',
            'category' => 'required|integer|exists:category,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $roleId = (int) $request->role;
        $categoryId = (int) $request->category;

        $webKeys = WebPlanKeysModel::select(['id', 'key', 'status'])
            ->where('status', 1)
            ->get()
            ->pluck('key', 'id');
        $activeWebKeyIds = $webKeys->keys()->map(fn($id) => (int) $id)->toArray();

        $plans = WebPlanModel::select([
                'id',
                'title',
                'short_description',
                'description',
                'status',
                'monthly_price',
                'quarterly_price',
                'yearly_price',
                'monthly_discount_percentage',
                'quarterly_discount_percentage',
                'yearly_discount_percentage',
                'monthly_final_amount',
                'quarterly_final_amount',
                'yearly_final_amount',
                'monthly_gst',
                'quarterly_gst',
                'yearly_gst',
            ])
            ->where('status', 1)
            ->where('role_id', $roleId)
            ->where('category_id', $categoryId)
            ->with(['getPlanKeyMap:key_id,plan_id'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($q) use ($activeWebKeyIds) {
                // Avoid IEEE-754 noise in JSON (long float tails) by rounding to 2 decimals via string.
                $roundMoney = function ($value) {
                    if ($value === null || $value === '') {
                        return null;
                    }

                    return json_decode(sprintf('%.2f', (float) $value));
                };

                $afterDiscount = function ($price, $discountPct) use ($roundMoney) {
                    if ($price === null || $price === '') {
                        return null;
                    }
                    $p = (float) $price;
                    $d = (float) ($discountPct ?? 0);

                    return $roundMoney($p * (1 - $d / 100));
                };

                return [
                    'plan' => [
                        'id' => $q->id,
                        'title' => $q->title,
                        'short_description' => $q->short_description,
                        'description' => $q->description,
                        'status' => $q->status,
                    ],
                    'pricing' => [
                        'monthly' => [
                            'price' => $q->monthly_price,
                            'discount_percentage' => $q->monthly_discount_percentage,
                            'afterDiscountValue' => round($afterDiscount($q->monthly_price, $q->monthly_discount_percentage)),
                            'final_amount' => round($roundMoney($q->monthly_final_amount)),
                            'gst' => $q->monthly_gst,
                        ],
                        'quarterly' => [
                            'price' => $q->quarterly_price,
                            'discount_percentage' => $q->quarterly_discount_percentage,
                            'afterDiscountValue' => round($afterDiscount($q->quarterly_price, $q->quarterly_discount_percentage)),
                            'final_amount' => round($roundMoney($q->quarterly_final_amount)),
                            'gst' => $q->quarterly_gst,
                        ],
                        'yearly' => [
                            'price' => $q->yearly_price,
                            'discount_percentage' => $q->yearly_discount_percentage,
                            'afterDiscountValue' => round($afterDiscount($q->yearly_price, $q->yearly_discount_percentage)),
                            'final_amount' => round($roundMoney($q->yearly_final_amount)),
                            'gst' => $q->yearly_gst,
                        ],
                    ],
                    'availableKeys' => $q->getPlanKeyMap
                        ->pluck('key_id')
                        ->map(fn($id) => (int) $id)
                        ->filter(fn($id) => in_array($id, $activeWebKeyIds))
                        ->values(),
                ];
            })
            ->toArray();

        return response()->json([
            'status' => true,
            'message' => 'Web plan list fetched successfully',
            'data' => [
                'role' => $roleId,
                'category' => $categoryId,
                'plans' => $plans,
                'webKeys' => $webKeys,
            ]
        ], 200);
    }

    /**
     * GET /api/portal/years/closure-status
     * Returns last 5 years with is_closed true/false depending on whether
     * all active rice forms have a closing recorded for that cropYear.
     */
    public function getYearClosureStatus()
    {
        // Take last 3 distinct crop years present in live_prices (desc)
        $years = \App\LivePrice::query()
            ->whereNotNull('cropYear')
            ->select('cropYear')
            ->distinct()
            ->orderBy('cropYear', 'desc')
            ->limit(3)
            ->pluck('cropYear')
            ->map(fn($y) => (int) $y)
            ->toArray();

        $data = array_map(function ($year) {
            // All (name, form) pairs that have any live price for this year
            $requiredPairs = \App\LivePrice::query()
                ->where('cropYear', $year)
                ->where('status', 1)
                ->whereNotNull('name')
                ->whereNotNull('form')
                ->where('closing', '!=', null)
                ->where('closing', '>', 0)
                ->first();

                $isClosed = ($year == 2023) ? true : ($requiredPairs !== null);

            return [
                'year' => (int) $year,
                'is_closed' => (bool) $isClosed,
            ];
        }, $years);

        return response()->json([
            'status' => true,
            'message' => 'Years closure status',
            'data' => $data
        ], 200);
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
                $addedDays = 30;
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
                $webUserAttachment = WebUserAttachment::where(['user_id' => $userId])->first();

                if($webUserAttachment == null || $webUserAttachment->panCard == null || $webUserAttachment->gstCard == null || $webUserAttachment->fssaiCard == null || $webUserAttachment->farmer_file == null ){
                    User::where(['id' => $userId])->update(['has_validation' => "Please submit your documents to complete your profile."]);
                }else{
                    User::where(['id' => $userId])->update(['has_validation' => "Your profile is under review. We will notify you once approved."]);
                }
                

                // send trial mail
                $mailTo = $userDetails->email;
                $mailMessage = '';
                $subject = 'Your SNTC 30-Day Free Trial is Now Active.';
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

    /**
     * Get current user session from cookie
     * GET /api/portal/session
     */
    public function getSession(Request $request)
    {
        // Debug: Check if cookie is being sent and session is being read
        \Log::info('=== Session Debug ===');
        \Log::info('Cookie Header: ' . ($request->header('Cookie') ?? 'NOT SENT'));
        \Log::info('Session ID: ' . $request->session()->getId());
        \Log::info('Session All: ' . json_encode($request->session()->all()));
        \Log::info('Auth Check: ' . (auth('web')->check() ? 'TRUE' : 'FALSE'));
        \Log::info('Origin: ' . $request->header('Origin'));
        
        // ✅ Use 'web' guard explicitly to ensure we're using session-based auth
        // Check if user is authenticated via session
        if (auth('web')->check()) {
            $user = auth('web')->user();
            
            // Ensure it's a web user (userType = 2)
            if ($user->userType != 2) {
                return response()->json(['status' => false, 'message' => 'Invalid user type'], 401);
            }
            
            // ✅ Reload user with relationships (can't use with() on existing model instance)
            $data = User::where('id', $user->id)->where('userType', 2)->with(['getWebPersonalDetails', 'getWebBusinessDetails' => function($q){
                return $q->with(['getCategoryDetails:id,category']);
            }, 'getWebUserAttachment','getWebUserSubscription' => function($q){
                return $q->whereDate('period_end' , '>=' , Carbon::now()->format('Y-m-d'));
            }, 'role_rel'])->first();

            // Check if user was found
            if (!$data) {
                return response()->json(['status' => false, 'message' => 'User not found'], 404);
            }

            $hasActivePlan = false;
            if($data->getWebUserSubscription){
                $hasActivePlan = true;
            }
            
            $hasBasicDetails = false;
            if ($data->getWebPersonalDetails != null || $data->getWebBusinessDetails != null || $data->getWebUserAttachment != null) {
                $hasBasicDetails = true;
            }

            $checkIfTrailDone = WebUserSubscriptionModel::where('user_id', $user->id)->where('subscription_type', 'trial')->first();
            
            $hasTrialDone = false;
            if($checkIfTrailDone){
                $hasTrialDone = true;
            }

            // Return same format as getUserDetails for consistency
            $userArray = $data->toArray();
            if (isset($userArray['get_web_business_details']['get_bag_vendor_web'])) {
                unset($userArray['get_web_business_details']['get_bag_vendor_web']);
            }

            return response()->json([
                'status' => true, 
                'message' => 'Session restored',
                'hasBasicDetails' => $hasBasicDetails,
                'hasTrialDone' => $hasTrialDone,
                'hasActivePlan' => $hasActivePlan,
                'planDetails' => $data->getWebUserSubscription,
                'data' => $userArray,
                'prefix' => [
                    'avatar' => 'webPortal/' . $user->id . '/attachments/avatar',
                    'gst' => 'webPortal/' . $user->id . '/attachments/gst',
                    'pan' => 'webPortal/' . $user->id . '/attachments/pan',
                    'fssai' => 'webPortal/' . $user->id . '/attachments/fssai'
                ]
            ], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'Not authenticated'], 401);
        }
    }

    /**
     * Logout user and clear session
     * POST /api/portal/logout
     */
    public function logout(Request $request)
    {
        auth('web')->logout();
        
        // Clear the session
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ], 200);
    }

    /**
     * Get web access permissions for a user
     * POST /api/portal/web-access
     * Payload: { "user_id": 123 }
     */
    public function getWebAccess(Request $request)
    {
        // Validate input
        $validator = \Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'plan_id' => 'nullable|integer|exists:web_plan,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user_id;

        // Get user with relationships
        $user = User::where('id', $userId)
            ->where('userType', 2)
            ->with([
                'getWebBusinessDetails' => function($q) {
                    return $q->with(['getCategoryDetails:id,category']);
                },
                'getWebUserSubscription' => function($q) {
                    return $q->whereDate('period_end', '>=', Carbon::now()->format('Y-m-d'))
                        ->orderBy('id', 'desc');
                },
                'role_rel:id,role_name'
            ])
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Get user's role
        $roleId = $user->role;
        if (!$roleId) {
            return response()->json([
                'status' => false,
                'message' => 'User role not found'
            ], 404);
        }

        // Get user's category from business details
        $categoryId = null;
        if ($user->getWebBusinessDetails && $user->getWebBusinessDetails->selected_category) {
            $categoryId = (int) $user->getWebBusinessDetails->selected_category;
        }

        // Check for active subscription (period_end >= today)
        $subscription = WebUserSubscriptionModel::where('user_id', $userId)
            ->whereDate('period_end', '>=', Carbon::now()->format('Y-m-d'))
            ->where(function ($q) {
                $q->where('status', 1)->orWhereNull('status');
            })
            ->orderBy('id', 'desc')
            ->first();
        
        if (!$subscription) {
            return response()->json([
                'status' => false,
                'message' => 'No active plan available'
            ], 200);
        }

        // Get plan_id from active subscription (optional override from client)
        $planId = $request->filled('plan_id')
            ? (int) $request->plan_id
            : (int) $subscription->plan_id;

        // All web_plan rows for this role + category (subscription plan may not match web_access.plan_id)
        $plansForRoleCategory = WebPlanModel::query()
            ->where('role_id', $roleId)
            ->where('status', 1)
            ->when($categoryId !== null, function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            }, function ($q) {
                $q->whereNull('category_id');
            })
            ->pluck('id');

        $candidatePlanIds = collect([$planId])
            ->merge($plansForRoleCategory)
            ->unique()
            ->filter()
            ->values()
            ->all();

        // Build query for web_access:
        // - match subscription / same role+category plan ids
        // - OR plan_id IS NULL (saved as "any plan" for that role/category in admin)
        $webAccessQuery = WebAccess::where('role_id', $roleId)
            ->where('status', 1)
            ->where(function ($q) use ($candidatePlanIds) {
                $q->whereIn('plan_id', $candidatePlanIds)
                    ->orWhereNull('plan_id');
            })
            ->with(['webSideMenu:id,title,sub_title,slug,create_url,read_url,update_url,delete_url,sort_order']);

        // Add category filter if category exists
        if ($categoryId) {
            $webAccessQuery->where(function($q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                  ->orWhereNull('category_id');
            });
        } else {
            $webAccessQuery->whereNull('category_id');
        }

        // Get web access permissions and order by menu sort_order
        $webAccess = $webAccessQuery->get()->unique('web_side_menu_id')->sortBy(function($access) {
            // Sort by menu's sort_order, then by menu id if sort_order is same
            if ($access->webSideMenu) {
                return [$access->webSideMenu->sort_order ?? 999999, $access->webSideMenu->id ?? 999999];
            }
            return [999999, 999999]; // Put items without menu at the end
        })->values(); // Reset keys after sorting

        // Format response (already sorted by sort_order)
        $permissions = $webAccess->map(function($access) {
            return [
                'menu_id' => $access->web_side_menu_id,
                'menu_title' => $access->webSideMenu ? $access->webSideMenu->title : null,
                'menu_sub_title' => $access->webSideMenu ? $access->webSideMenu->sub_title : null,
                'menu_slug' => $access->webSideMenu ? $access->webSideMenu->slug : null,
                'menu_sort_order' => $access->webSideMenu ? $access->webSideMenu->sort_order : null,
                'urls' => [
                    'create_url' => $access->webSideMenu ? $access->webSideMenu->create_url : null,
                    'read_url' => $access->webSideMenu ? $access->webSideMenu->read_url : null,
                    'update_url' => $access->webSideMenu ? $access->webSideMenu->update_url : null,
                    'delete_url' => $access->webSideMenu ? $access->webSideMenu->delete_url : null,
                ],
                'permissions' => [
                    'can_create' => (bool) $access->can_create,
                    'can_read' => (bool) $access->can_read,
                    'can_update' => (bool) $access->can_update,
                    'can_delete' => (bool) $access->can_delete,
                ]
            ];
        });

        // Last 4 crop years from live_prices and access per plan (allowed_years)
        $lastYears = \App\LivePrice::query()
            ->whereNotNull('cropYear')
            ->select('cropYear')
            ->distinct()
            ->orderBy('cropYear', 'desc')
            ->limit(3)
            ->pluck('cropYear')
            ->map(fn($y) => (int) $y)
            ->toArray();
        $allowedYearsUnion = collect($webAccess)->pluck('allowed_years')->filter()->flatten()->map(fn($y) => (int) $y)->unique()->toArray();
        $yearsAccess = array_map(function($y) use ($allowedYearsUnion){
            $closedRow = \App\LivePrice::query()
                ->where('cropYear', $y)
                ->where('status', 1)
                ->whereNotNull('name')
                ->whereNotNull('form')
                ->where('closing', '!=', null)
                ->where('closing', '>', 0)
                ->first();
            return [
                'year' => (int)$y,
                'has_access' => in_array((int)$y, $allowedYearsUnion),
                'is_closed' => $closedRow !== null
            ];
        }, $lastYears);

        // Fetch plan name
        $planTitle = null;
        if ($planId) {
            $planTitle = \App\WebPlanModel::where('id', $planId)->value('title');
        }

        return response()->json([
            'status' => true,
            'message' => 'Web access permissions retrieved successfully',
            'data' => [
                'user_id' => $userId,
                'plan_id' => $planId,
                'plan_name' => $planTitle,
                'role' => [
                    'id' => $roleId,
                    'name' => $user->role_rel ? $user->role_rel->role_name : null
                ],
                'category' => [
                    'id' => $categoryId,
                    'name' => $user->getWebBusinessDetails && $user->getWebBusinessDetails->getCategoryDetails 
                        ? $user->getWebBusinessDetails->getCategoryDetails->category 
                        : null
                ],
                'plan' => [
                    'id' => $planId,
                    'subscription_type' => $subscription->subscription_type,
                    'period_start' => $subscription->period_start,
                    'period_end' => $subscription->period_end
                ],
                'web_access' => $permissions,
                'years' => $yearsAccess
            ]
        ], 200);
    }

}
