<?php

namespace App\Http\Controllers;

use App\WebVendorCategory;
use App\CategoryRoleMap;
use App\Courier;
use App\LivePrice;
use App\LivePricesOpeningClosing;
use App\MillStatus;
use App\Packing;
use App\PackingType;
use App\Quality;
use App\Repositories\CourierRepository;
use App\Sample;
use App\User;
use App\Designation;
use App\ChartInterval;
use App\Port;
use App\PortImages;
use App\Category;
use App\Gallery;
use App\Grade;
use App\Contact;
use App\RiceName;
use App\RiceType;
use App\Testimonial;
use App\TestimonialVideo;
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
use App\FutureBuyQueriesINR;
use App\FutureSellQueriesINR;
use App\Helpers\StatusChat;
use App\BrandAvailability;
use App\USD_prices;
// use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;
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
use App\LivePriceStatusMessage;
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
use App\WebNewsRunner;
use App\TradeCurrentStatus;
use App\PostedJob;
use App\JobApplication;
use App\TradeCategoryMap;
use App\WebBusinessDetails;
use App\Services\UserInterestService;
use App\AvgLengthMap;
use App\WebRiceFormMap;


class ApiController extends Controller
{
    /**
     * Latest live-price edit time across all states (not per viewed state).
     * Optionally limited to a crop year when the API is year-scoped.
     */
    protected function livePricesGlobalLastUpdatedAtFormatted($cropYear = null): string
    {
        $appTz = config('app.timezone', 'Asia/Kolkata');
        $q = LivePrice::query()
            ->where('name', '!=', '0')
            ->where('form', '!=', '0')
            ->whereNotNull('min_price')
            ->whereNotNull('max_price');

        if ($cropYear !== null && $cropYear !== '' && is_numeric($cropYear)) {
            $q->where('cropYear', (int) $cropYear);
        }

        $row = $q->orderByDesc('updated_at')->orderByDesc('id')->first();

        return ($row && $row->updated_at)
            ? $row->updated_at->copy()->setTimezone($appTz)->format('d-M-Y, g:i A')
            : '';
    }

    public function getWebOtherServices()
    {
        $bagVendor = BagVendors::vendorType();

        return response()->json(['status' => true , 'messages' => 'Bag vendoe get successfully' , 'data' => $bagVendor ]);
    }

    //Validate
    public static function apiValidation($request, $required)
    {
        $errorBag = [];
        foreach ($required as $key => $input) {
            if (!array_key_exists($input, $request) || ($request[$input] == '' || $request[$input] == null)) {
                $errorBag[] = [$input => $input . ' value is required!'];
            }
        }
        if (!empty($errorBag)) {
            $response = ['status' => 'error', 'message' => 'required fields are missing!', 'errors' => $errorBag];
            return $response;
        }
    }

    public function getChatStatus()
    {
        return ['status' => 'success', 'data' => StatusChat::getStatus()];
    }

    public static function sendGCM($message)
    {

        $url = 'https://fcm.googleapis.com/fcm/send';
        $fields = [
            'registration_ids' => [
                'cdtlbIZUReSKl4xkn0SfKr:APA91bEezbuCTnLh4E3DxulE_8zYDwJLijd3ksGkdUtV0JFxU_il3Fdim_7FbTfpu1oM0EYdrS2oB05BGZgz6GhnrW8R1i7LEwKffEbFGpxPaNrSR5LHQ23LWKFcsN789FMmzscRyJRH'
            ],
            'data' => ["message" => $message]
        ];
        $fields = json_encode($fields);

        $headers = array(
            'Authorization: key=AAAA10hB_8I:APA91bHVSnAJjacznL6i3p9dWnKvJeceYJlTbwt_rvyq6Nx8tOPsMlxtYPqHzAJRAazC5JJof9PZHaw_uo1qbNkKK4YgJLKN_39ozcIlbCpt3YQ36Y5rT6ftegC0nnEiOZ-dYsYqFWcV',
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * Lightweight session probe for the native app.
     */
    public function checkAppSession(Request $request)
    {
        $user = $request->user();
        $sessionExpired = false;
        $sessionVersion = null;

        if ($user) {
            $sessionVersion = (int) ($user->session_version ?? 0);
            $sessionExpired = $this->isAppSessionExpired($request, $user);
        }

        return response()->json([
            'status' => true,
            'session_expired' => $sessionExpired,
            'session_version' => $sessionVersion,
            'user_id' => $user ? (int) $user->id : null,
            'platform' => 'mobile',
        ]);
    }

    public function login(Request $request)
    {
        $userModel = User::where(['email' => $request->email , 'userType' => 1])->with(['role_rel', 'role_rel_usd'])->first();
        if ($userModel == null) {
            $userModel = User::where(['mobile' => $request->email , 'userType' => 1])->with(['role_rel', 'role_rel_usd'])->first();
        }

        if ($userModel == null) {
            return response()->json(['status' => 'error', 'message' => 'Wrong user detail']);
        }

        // Check if user account is deactivated or rejected 
        if ($blockedMessage = $userModel->authAccessBlockedMessage()) {
            return response()->json([
                'status' => 'error',
                'message' => $blockedMessage,
            ]);
        }

        $oldPassword = $userModel->password;

        if (Hash::check($request->password, $oldPassword)) {
            $random_token = $this->rotateAppSessionForUser($userModel);

            if ($userModel->is_usd_active == 0) {
                if ($userModel->is_INR_active == 0) {

                    $checkuser = User::where(['email' => $request->email , 'userType' => 1])->first();
                    if ($checkuser == null) {
                        User::where(['mobile' => $request->email , 'userType' => 1])->update(['is_INR_active' => 1]);
                    } else {
                        User::where(['email' => $request->email , 'userType' => 1])->update(['is_INR_active' => 1]);
                    }
                }
            }
            
            $userModel = User::where(['email' => $request->email , 'userType' => 1])->with(['role_rel', 'role_rel_usd'])->first();
            if ($userModel == null) {
                $userModel = User::where(['mobile' => $request->email , 'userType' => 1])->with(['role_rel', 'role_rel_usd'])->first();
            }
            if ($userModel->status == 0) {
                $Newotp = $userModel->otp;
                $mobile = $userModel->mobile;
                file_get_contents('http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto=' . $mobile . '&message=Thank+you+for+registering+on+SNTC+Rice+Live+Pricing+App.+Your+OTP+Code+is+'.$Newotp.'.+SNTCAL&PEID=1701172916686910712&templateid=1707172924575773908');

                if ($userModel->email != null) {
                    $response = MailController::generateMailForOTPThanks($userModel->email, 'no@replay.in', 'SNTC GROUP', 'Thank you for registering on SNTC Rice Live Pricing App.', 'Thank you for registering on SNTC Rice Live Pricing App.', $Newotp);
                }

                return $this->appLoginSuccessResponse($userModel, $random_token);
            }
            return $this->appLoginSuccessResponse($userModel, $random_token);
        } else {
            return response()->json(['status' => 'error', 'test' => 1, 'message' => 'Wrong user detail']);
        }
    }

    /**
     * Invalidate previous app device session and issue a new API token.
     * Used by password login and OTP send/verify.
     */
    private function rotateAppSessionForUser(User $user): string
    {
        do {
            $random_token = hash('sha256', Str::random(80) . microtime(true) . $user->id . 'mobile');
        } while (
            User::where('api_token', $random_token)->exists()
            || User::where('mobile_api_token', $random_token)->exists()
        );

        $previousFcm = trim((string) ($user->user_token ?? ''));
        $newSessionVersion = (int) ($user->session_version ?? 0) + 1;

        User::where('id', $user->id)->update([
            'mobile_api_token' => $random_token,
            'api_token' => $random_token,
            'session_version' => $newSessionVersion,
        ]);

        $user->mobile_api_token = $random_token;
        $user->api_token = $random_token;
        $user->session_version = $newSessionVersion;

        $this->sendAppForceLogoutPush($previousFcm);

        return $random_token;
    }

    /**
     * Login payload with token fields legacy apps can persist (bypasses User::$hidden).
     */
    private function appLoginSuccessResponse(User $user, string $token)
    {
        $userArr = $user->toArray();
        $sessionVersion = (int) ($user->session_version ?? 0);
        $userArr['api_token'] = $token;
        $userArr['token'] = $token;
        $userArr['session_version'] = $sessionVersion;

        return response()->json([
            'status' => 'success',
            'user' => $userArr,
            'token' => $token,
            'api_token' => $token,
            'session_version' => $sessionVersion,
            'platform' => 'mobile',
        ]);
    }

    /**
     * Data-only FCM so the previous phone can clear local session.
     */
    private function sendAppForceLogoutPush(string $fcmToken): void
    {
        if ($fcmToken === '') {
            return;
        }

        try {
            $messaging = Firebase::messaging();
            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withData([
                    'type' => 'force_logout',
                    'session_expired' => 'true',
                ]);
            $messaging->send($message);
        } catch (\Throwable $e) {
            Log::warning('App force_logout FCM failed: ' . $e->getMessage());
        }
    }

    /**
     * True when the client token / session_version does not match the current server session.
     */
    private function isAppSessionExpired(Request $request, User $user): bool
    {
        $token = null;
        $authHeader = $request->header('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            $token = trim($matches[1]);
        }
        if (! $token) {
            $token = $request->header('X-API-TOKEN');
        }
        if (! $token) {
            $token = $request->input('api_token')
                ?? $request->input('token')
                ?? $request->query('api_token')
                ?? $request->query('token');
            $token = $token !== null ? trim((string) $token) : null;
        }

        if ($token !== null && $token !== '') {
            $mobileToken = (string) ($user->mobile_api_token ?? '');
            $apiToken = (string) ($user->api_token ?? '');
            $matchesMobile = $mobileToken !== '' && hash_equals($mobileToken, $token);
            $matchesApi = $apiToken !== '' && hash_equals($apiToken, $token);
            if (! $matchesMobile && ! $matchesApi) {
                return true;
            }
        }

        $clientVersion = $request->input('session_version', $request->query('session_version'));
        if ($clientVersion !== null && $clientVersion !== '') {
            if ((int) $clientVersion !== (int) ($user->session_version ?? 0)) {
                return true;
            }
        }

        return false;
    }



    public function sendOTP($number, $isOTP = false)
    {
        $otp = rand(1111, 9999);
        $user = User::where('mobile', $number)->where('userType', 1)->where('status', 1)->first();
        if ($user == null) {
            $user = User::where('mobile', $number)->where('status', 1)->first();
        }
        if ($user != null) {
            // Expire any previous app session immediately when OTP is requested.
            $sessionToken = ((int) ($user->userType ?? 0) === 1)
                ? $this->rotateAppSessionForUser($user)
                : null;

            User::where('id', $user->id)->update(['otp' => $otp]);

            $message = "Dear Customer, Your SNTC live pricing premium membership is now active, we are so excited to unlock PREMIUM benefits for you , Enjoy free live prices for the all the rice products. TCA.";
            $response = null;
            if ($isOTP == true) {
                file_get_contents('http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto=' . $number . '&message=' . urlencode($message));
                if ($user->email != null) {
                    $response = MailController::generateMail($user->email, 'no@replay.in', 'SNTC GROUP', $message, 'SNTC Live Pricing Premium Membership ');
                }
            }

            if ($isOTP == false) {
                file_get_contents('http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto=' . $number . '&message=Your%20forgot%20password%20OTP%20for%20SNTC%20Rice%20Live%20Pricing%20App%20is+' . $otp . '.SNTCAL&PEID=1701172916686910712&templateid=1707172924586815812');
                if ($user->email != null) {
                    $response = MailController::generateMailForOTP($user->email, 'no@replay.in', 'SNTC GROUP', null, 'SNTC OTP Verification ', $otp);
                }
            }

            $user = User::where('id', $user->id)->first();
            $userArr = $user ? $user->toArray() : [];
            if ($sessionToken !== null) {
                $userArr['api_token'] = $sessionToken;
                $userArr['token'] = $sessionToken;
                $userArr['session_version'] = (int) ($user->session_version ?? 0);
            }

            return response()->json([
                'error' => null,
                'data' => $otp,
                'mailResponse' => $response,
                'user' => $userArr,
                'token' => $sessionToken,
                'api_token' => $sessionToken,
                'session_version' => $sessionToken !== null ? (int) ($user->session_version ?? 0) : null,
                'platform' => 'mobile',
            ], 200);
        } else {
            return response()->json(['error' => 'No record available for ' . $number, 'user' => $user], 500);
        }
    }


    public function resendOTP($number)
    {
        $otp = rand(1111, 9999);
        $user = User::where('mobile', $number)->where('userType', 1)->first();
        if ($user == null) {
            $user = User::where('mobile', $number)->first();
        }
        if ($user == null) {
            return response()->json(['error' => 'No record available for ' . $number, 'data' => null], 500);
        }

        $sessionToken = ((int) ($user->userType ?? 0) === 1)
            ? $this->rotateAppSessionForUser($user)
            : null;

        User::where('id', $user->id)->update(['otp' => $otp]);

        $mobile = $user->mobile;
        file_get_contents('http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto=' . $mobile . '&message=Thank+you+for+registering+on+SNTC+Rice+Live+Pricing+App.+Your+OTP+Code+is+'.$otp.'.+SNTCAL&PEID=1701172916686910712&templateid=1707172924575773908');
        $response = null;
        if ($user->email != null) {
            $response = MailController::generateMailForOTPThanks($user->email, 'no@replay.in', 'SNTC GROUP', 'Thank you for registering on SNTC Rice Live Pricing App.', 'Thank you for registering on SNTC Rice Live Pricing App.', $otp);
        }

        $user = User::where('id', $user->id)->first();
        $userArr = $user ? $user->toArray() : [];
        if ($sessionToken !== null) {
            $userArr['api_token'] = $sessionToken;
            $userArr['token'] = $sessionToken;
            $userArr['session_version'] = (int) ($user->session_version ?? 0);
        }

        return response()->json([
            'error' => null,
            'data' => $otp,
            'mailResponse' => $response,
            'user' => $userArr,
            'token' => $sessionToken,
            'api_token' => $sessionToken,
            'session_version' => $sessionToken !== null ? (int) ($user->session_version ?? 0) : null,
            'platform' => 'mobile',
        ], 200);
    }



    public function preLoadSampleEntryContent()
    {
        $sellerModel = User::whereRole(4)->pluck('name', 'id');
        $qualityModel = Quality::qualities();
        $packingModel = Packing::pluck('code', 'id');
        $packingTypeModel = PackingType::pluck('name', 'id');
        return response()->json([
            'status' => 'success',
            'seller' => $sellerModel,
            'quality' => $qualityModel,
            'packing' => $packingModel,
            'packing_type' => $packingTypeModel
        ]);
    }

    public function saveSampleEntry(Request $request)
    {
        $folderPath = 'sample-images/';
        $image_parts = explode(";base64,", $request->image);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $file = uniqid() . '.' . $image_type;
        file_put_contents($folderPath . $file, $image_base64);
        $sampleModel = new Sample();
        $sampleModel->date = Carbon::parse($request->date)->format('Y-m-d');
        $sampleModel->photo = $file;
        $sampleModel->packing = $request->packing;
        $sampleModel->packing_type = $request->packing_type;
        $sampleModel->quality = $request->quality;
        $sampleModel->supplier = $request->seller;
        $sampleModel->no_of_bags = $request->no_of_bags;
        $sampleModel->bags_qty = $request->bags_qty;
        $sampleModel->qty = $request->qty;
        $sampleModel->save();
        return response()->json(['status' => 'success', 'sample' => $sampleModel]);
    }

    public function pendingCourierSamples(Request $request)
    {
        $sampleModel = Sample::with(['supplier_rel', 'quality_rel', 'packing_rel', 'packing_type_rel'])
            ->whereCourierStatus(0)->get();
        $sentVia = Courier::$sentVia;
        return response()->json(['status' => 'success', 'samples' => $sampleModel, 'sent_via' => $sentVia]);
    }

    public function saveCourier(Request $request)
    {
        $courierModel = new Courier();
        $courierModel->date = Carbon::parse($request->date)->format('Y-m-d');
        $courierModel->samples = json_encode($request->sample);
        $courierModel->sent_via = $request->sent_via;
        $courierModel->details = $request->details;
        $courierModel->save();
        CourierRepository::updateSamples($request, $courierModel);
        return response()->json(['status' => 'success', 'courier' => $courierModel]);
    }

    public function saveMillStatus(Request $request)
    {
        $millStatusModel = new MillStatus;
        $millStatusModel->date = Carbon::parse($request->date)->format('Y-m-d');
        $millStatusModel->seller = $request->seller;
        $millStatusModel->visit_status = $request->visit_status;
        $millStatusModel->remarks = $request->remarks;
        $millStatusModel->save();
        return response()->json(['status' => 'success', 'mill_status' => $millStatusModel]);
    }

    public function getPlans()
    {
        $plan = Plan::get();
        $ChartInterval = ChartInterval::get();

        $SubPlans = SubPlan::get();
        $sub_plan = [];
        $chart_int = [];
        $data = [];

        foreach ($plan as $k => $v) {
            $data[$v->plan_name]['plan'] = $v;

            $sub_plan[] = json_decode($v->sub_plan, true);
            $chart_int = json_decode($v->chart_int, true);

            $SubPlan = [];
            foreach ($sub_plan as $key => $value) {
                foreach ($value as $ke => $val) {
                    $SubPlan[$ke]['data'] = SubPlan::where(['id' => $ke])->first();
                    $SubPlan[$ke]['price'] = $val;
                }
            }

            $data[$v->plan_name]['SubPlan'] = $SubPlan;

            // $data[$v->plan_name]['ChartInt'] = ChartInterval::select('id', 'name')->whereIn('id' , array_values($chart_int))->get();
        }
        $chartInt = ChartInterval::select('id')->whereIn('id', array_values($chart_int))->get()->pluck('id');
        $chartIntArray = $chartInt->toArray();
        // dd([$data ,$SubPlans , $SubPlan, $ChartInterval, $plan , $chartInt,$chartIntArray]);
        return response()->json(['status' => 'success', 'plans' => $data], 200);
        // return view('plans.edit', compact('data' ,'SubPlans' , 'SubPlan', 'ChartInterval', 'plan' , 'chartInt','chartIntArray'));
        //        $listPlans = Plan::with(['sub_plan' , 'ChartInterval'])->get();
        $listPlans = Plan::get();
        //        dd($listPlans);
        return response()->json(['status' => 'success', 'plans' => $listPlans], 200);
    }

    // public function getPrices($state, $ricetype)
    // {

    //     $replacehiphen = explode('-', $ricetype);
    //     $replaceWithUnderscore = implode('_', $replacehiphen);

    //     $processedData = [];
    //     $lastRecord = LivePrice::where('name' ,'!=', '0')->where('form' , '!=' , '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('id' , 'DESC')->first();
    //     // dd($lastRecord);
    //     if ($lastRecord != null) {

    //         $prices = LivePrice::where('name' ,'!=', '0')->where('form' , '!=' , '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->with(['name_rel' => function($query) use($ricetype){
    //             return $query->where('type' , $ricetype)->get();
    //         },'form_rel' => function ($query) use ($ricetype) {
    //                 return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
    //             }
    //         ])->where('state' , $state)->whereDate('created_at',$lastRecord->created_at->format('Y-m-d'))->get();

    //         $lastToLastDate = LivePrice::where('name' ,'!=', '0')->where('form' , '!=' , '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('created_at', 'DESC')->whereDate('created_at', '<',$lastRecord->created_at->format('Y-m-d'))->get();

    //         if (!$lastToLastDate->isEmpty()) {
    //             $pricesprevious = LivePrice::where('min_price', '!=', null)->where('max_price', '!=', null)->with([
    //                 'name_rel' => function($query){
    //                     return $query->get();
    //                 }, 'form_rel' => function ($query) use ($ricetype) {
    //                     return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
    //                 }
    //             ])->where(['state' => $state])->where(DB::raw('date(created_at)'),
    //                 $lastToLastDate[0]->created_at->format('Y-m-d'))->get();

    //             $data = LivePrice::where('min_price', '!=', null)->where('max_price', '!=', null)->with([
    //                 'name_rel' => function($query){
    //                     // return $query->orderBy('order', 'asc')->get();
    //                     return $query->get();
    //                 },'form_rel' => function ($query) use ($ricetype) {
    //                     return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
    //                 }
    //             ])->where(['state' => $state])->where(DB::raw('date(created_at)'),
    //                 $lastRecord->created_at->format('Y-m-d'))->orWhere(DB::raw('date(created_at)'),
    //                 $lastToLastDate[0]->created_at->format('Y-m-d'))->get();

    //             foreach ($data->sortBy('name_rel.order') as $k => $v) {
    //                 if ($v->name_rel != null && $v->state != null && $v->form_rel != null) {
    //                     if ($state == $v->state) {
    //                         $replaceHignfn = explode('-', $v->name_rel->type);
    //                         $implodeUnderscore = implode('_', $replaceHignfn);
    //                         $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
    //                     }
    //                 }
    //             }

    //             $fiilteredProcessedData = [];
    //             foreach ($data->sortBy('form_rel.order') as $k => $v) {
    //                 if ($v->name_rel != null && $v->state != null && $v->form_rel != null) {
    //                     if ($state == $v->state) {

    //                         $replaceHignfn = explode('-', $v->name_rel->type);
    //                         $implodeUnderscore = implode('_', $replaceHignfn);
    //                         $fiilteredProcessedData[$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
    //                     }
    //                 }
    //             }
    //             $newProcessed = [];

    //             foreach($processedData as $k => $v){
    //                 foreach($v as $kk => $vv){
    //                     $processedData[$k][$kk] = $fiilteredProcessedData[$kk];
    //                 }
    //             }

    //             $latstRecord = $lastRecord->created_at->format('Y-m-d');

    //             $newProcessedData = [];

    //             // foreach($processedData as $k => $v){
    //             //     $riceType = $k;
    //             //     if( is_array($v) ){
    //             //         foreach($v as $kk => $vv){
    //             //             if( $kk != '' ){
    //             //                 if( is_array($vv) ){
    //             //                     foreach($vv as $kkk => $vvv){
    //             //                         $newProcessedData[$riceType][$kkk] = $vvv;    
    //             //                     }
    //             //                 }
    //             //             }
    //             //         }    
    //             //     }
    //             // }
    //             foreach ($processedData as $k => $v) {
    //                 if (is_array($v)) {
    //                     foreach ($v as $key => $value) {
    //                         if (is_array($value)) {
    //                             foreach ($value as $ke => $val) {
    //                                 if (!array_key_exists($latstRecord, $val)) {
    //                                     unset($processedData[$k][$key][$ke]);
    //                                 }
    //                             }
    //                         }
    //                     }
    //                 }
    //             }


    //             foreach ($processedData as $k => $v) {
    //                 if (is_array($v)) {
    //                     foreach ($v as $key => $val) {
    //                         if (empty($val)) {
    //                             unset($processedData[$k][$key]);
    //                         }else{
    //                             foreach($val as $kk => $vv ){
    //                                 // dd($processedData[$k][$key][$kk] );
    //                                 if( $kk != 0 ){
    //                                   $processedData[$k][$key][$kk]['isHide'] = 'true'; 
    //                                 }
    //                             }
    //                         }
    //                     }
    //                 }
    //             }

    //             $newProccessedData = [];

    //             $newData = collect($processedData)->map(function($item){
    //                 return collect($item)->map(function($innerItem) use ($item){
    //                     $onlyValues = array_values($innerItem);
    //                     $onlyKeys = array_keys($innerItem);
    //                     foreach($onlyValues as $k => $v){
    //                         if( $k == 0 ){
    //                             $onlyValues[$k]['is_hide'] = 'false';        
    //                         }else{
    //                             $onlyValues[$k]['is_hide'] = 'true';
    //                         }
    //                     }


    //                     $data = array_combine( $onlyKeys, $onlyValues);
    //                     return $data;
    //                 });
    //             })->toArray();

    //             $order = [];
    //             foreach($newData as $k => $v){
    //                 foreach($v as $kk => $vv){
    //                     $order[$k][] = [ $kk => $vv] ;
    //                 }
    //             }

    //             $myNewData = [];
    //             foreach($order as $k => $v){
    //                 foreach($v as $kk => $vv){
    //                     $newDataProcess = [];
    //                     foreach($vv as $key => $value){
    //                         foreach($value as $ke => $val){
    //                             $newDataProcess[] = [$ke => $val];   
    //                         }
    //                         $myNewData[$k][$kk][$key] = $newDataProcess;
    //                     }
    //                 }
    //             }

    //             // $newData['order'] = $order;
    //             // $processedResponse = $newData->toArray();
    //             return response()->json([
    //                 'errors' => null,
    //                 'prices' => $myNewData,
    //                 'latest' => $lastRecord->created_at->format('Y-m-d'),
    //                 'lastUpdatedDate' => $lastRecord->created_at->format('d-M-Y, g:i A'),
    //                 'oldDate' => $lastToLastDate[0]->created_at->format('Y-m-d')
    //             ]);
    //         }

    //         foreach ($prices as $k => $v) {
    //             if ($v->name_rel != null && $v->state != null && $v->form_rel != null) {
    //                 if ($state == $v->state) {
    //                     $replaceHignfn = explode('-', $v->name_rel->type);
    //                     $implodeUnderscore = implode('_', $replaceHignfn);
    //                     $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
    //                 }

    //             }
    //         }

    //         return response()->json([
    //             'errors' => null,
    //             'prices' => json_encode($processedData),
    //             'latest' => $lastRecord->created_at->format('d-M-Y, g:i A'),
    //             'oldDate' => ''
    //         ]);

    //     } else {
    //         print_r('kjhnjki');
    //         die();
    //         $data = LivePrice::where('state' , $state)->where('min_price', '!=', null)->where('max_price', '!=', null)->with([
    //             'name_rel',
    //             'form_rel' => function ($query) use ($ricetype) {
    //                 return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
    //             }
    //         ])->where(['state' => $state])->where(DB::raw('date(created_at)'),
    //             Carbon::now()->format('Y-m-d'))->get();

    //         foreach ($data as $k => $v) {
    //             if ($v->name_rel != null && $v->state != null && $v->form_rel != null) {
    //                 if ($state == $v->state) {
    //                     $replaceHignfn = explode('-', $v->name_rel->type);
    //                     $implodeUnderscore = implode('_', $replaceHignfn);
    //                     $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
    //                 }

    //             }
    //         }
    //         return response()->json([
    //             'errors' => null,
    //             'prices' => $processedData,
    //             'last_updated_record' => $latstRecord,
    //             'latest' => '',
    //             'oldDate' => ''
    //         ]);
    //     }

    // }

    public function getPrices_old($state, $ricetype)
    {

        $replacehiphen = explode('-', $ricetype);
        $replaceWithUnderscore = implode('_', $replacehiphen);

        $processedData = [];
        $lastRecord = LivePrice::where('name', '!=', '0')->where('form', '!=', '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('id', 'DESC')->first();

        if ($lastRecord != null) {

            $prices = LivePrice::where('name', '!=', '0')->where('form', '!=', '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->with([
                'name_rel' => function ($query) use ($ricetype) {
                    return $query->where('type', $ricetype)->get();
                },
                'form_rel' => function ($query) use ($ricetype) {
                    return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
                }
            ])->where('state', $state)->get();

            $lastToLastDate = LivePrice::where('name', '!=', '0')->where('form', '!=', '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('created_at', 'DESC')->whereDate('created_at', '<', $lastRecord->created_at->format('Y-m-d'))->get();

            if (!$lastToLastDate->isEmpty()) {
                $pricesprevious = LivePrice::where('min_price', '!=', null)->where('max_price', '!=', null)->with([
                    'name_rel' => function ($query) {
                        return $query->get();
                    },
                    'form_rel' => function ($query) use ($ricetype) {
                        return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
                    }
                ])->where(['state' => $state])->where(
                    DB::raw('date(created_at)'),
                    $lastToLastDate[0]->created_at->format('Y-m-d')
                )->get();

                $data = LivePrice::where('min_price', '!=', null)->where('max_price', '!=', null)->with([
                    'name_rel' => function ($query) {
                        // return $query->orderBy('order', 'asc')->get();
                        return $query->get();
                    },
                    'form_rel' => function ($query) use ($ricetype) {
                        return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
                    }
                ])->where(['state' => $state])->where(
                    DB::raw('date(created_at)'),
                    $lastRecord->created_at->format('Y-m-d')
                )->orWhere(
                    DB::raw('date(created_at)'),
                    $lastToLastDate[0]->created_at->format('Y-m-d')
                )->get();

                foreach ($data->sortBy('name_rel.order') as $k => $v) {
                    if ($v->name_rel != null && $v->state != null && $v->form_rel != null) {
                        if ($state == $v->state) {
                            $replaceHignfn = explode('-', $v->name_rel->type);
                            $implodeUnderscore = implode('_', $replaceHignfn);
                            $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                        }
                    }
                }

                $fiilteredProcessedData = [];
                foreach ($data->sortBy('form_rel.order') as $k => $v) {
                    if ($v->name_rel != null && $v->state != null && $v->form_rel != null) {
                        if ($state == $v->state) {

                            $replaceHignfn = explode('-', $v->name_rel->type);
                            $implodeUnderscore = implode('_', $replaceHignfn);
                            $fiilteredProcessedData[$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                        }
                    }
                }
                $newProcessed = [];

                foreach ($processedData as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $processedData[$k][$kk] = $fiilteredProcessedData[$kk];
                    }
                }

                $latstRecord = $lastRecord->created_at->format('Y-m-d');

                $newProcessedData = [];

                // foreach($processedData as $k => $v){
                //     $riceType = $k;
                //     if( is_array($v) ){
                //         foreach($v as $kk => $vv){
                //             if( $kk != '' ){
                //                 if( is_array($vv) ){
                //                     foreach($vv as $kkk => $vvv){
                //                         $newProcessedData[$riceType][$kkk] = $vvv;    
                //                     }
                //                 }
                //             }
                //         }    
                //     }
                // }
                foreach ($processedData as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $key => $value) {
                            if (is_array($value)) {
                                foreach ($value as $ke => $val) {
                                    if (!array_key_exists($latstRecord, $val)) {
                                        unset($processedData[$k][$key][$ke]);
                                    }
                                }
                            }
                        }
                    }
                }


                foreach ($processedData as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $key => $val) {
                            if (empty($val)) {
                                unset($processedData[$k][$key]);
                            } else {
                                foreach ($val as $kk => $vv) {
                                    // dd($processedData[$k][$key][$kk] );
                                    if ($kk != 0) {
                                        $processedData[$k][$key][$kk]['isHide'] = 'true';
                                    }
                                }
                            }
                        }
                    }
                }

                $newProccessedData = [];

                $newData = collect($processedData)->map(function ($item) {
                    return collect($item)->map(function ($innerItem) use ($item) {
                        $onlyValues = array_values($innerItem);
                        $onlyKeys = array_keys($innerItem);
                        foreach ($onlyValues as $k => $v) {
                            if ($k == 0) {
                                $onlyValues[$k]['is_hide'] = 'false';
                            } else {
                                $onlyValues[$k]['is_hide'] = 'true';
                            }
                        }


                        $data = array_combine($onlyKeys, $onlyValues);
                        return $data;
                    });
                })->toArray();

                $order = [];
                foreach ($newData as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $order[$k][] = [$kk => $vv];
                    }
                }

                $myNewData = [];
                foreach ($order as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $newDataProcess = [];
                        foreach ($vv as $key => $value) {
                            foreach ($value as $ke => $val) {
                                $newDataProcess[] = [$ke => $val];
                            }
                            $myNewData[$k][$kk][$key] = $newDataProcess;
                        }
                    }
                }

                // $newData['order'] = $order;
                // $processedResponse = $newData->toArray();
                return response()->json([
                    'errors' => null,
                    'prices' => $myNewData,
                    'latest' => $lastRecord->created_at->format('Y-m-d'),
                    'lastUpdatedDate' => $this->livePricesGlobalLastUpdatedAtFormatted(null),
                    'oldDate' => $lastToLastDate[0]->created_at->format('Y-m-d')
                ]);
            }

            foreach ($prices as $k => $v) {
                if ($v->name_rel != null && $v->state != null && $v->form_rel != null) {
                    if ($state == $v->state) {
                        $replaceHignfn = explode('-', $v->name_rel->type);
                        $implodeUnderscore = implode('_', $replaceHignfn);
                        $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                    }
                }
            }

            return response()->json([
                'errors' => null,
                'prices' => json_encode($processedData),
                'latest' => $lastRecord->created_at->format('d-M-Y, g:i A'),
                'oldDate' => ''
            ]);
        } else {
            print_r('kjhnjki');
            die();
            $data = LivePrice::where('state', $state)->where('min_price', '!=', null)->where('max_price', '!=', null)->with([
                'name_rel',
                'form_rel' => function ($query) use ($ricetype) {
                    return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
                }
            ])->where(['state' => $state])->where(
                DB::raw('date(created_at)'),
                Carbon::now()->format('Y-m-d')
            )->get();

            foreach ($data as $k => $v) {
                if ($v->name_rel != null && $v->state != null && $v->form_rel != null) {
                    if ($state == $v->state) {
                        $replaceHignfn = explode('-', $v->name_rel->type);
                        $implodeUnderscore = implode('_', $replaceHignfn);
                        $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                    }
                }
            }
            return response()->json([
                'errors' => null,
                'prices' => $processedData,
                'last_updated_record' => $latstRecord,
                'latest' => '',
                'oldDate' => ''
            ]);
        }
    }

    public function getPricesByYear($state, $ricetype)
    {
        $startYear = '';
        $endYear = '';

        if( isset($_GET['year']) ){
            $selectedYear = explode( '-' , $_GET['year']);
            $startYear = $selectedYear[0];
            $endYear = $selectedYear[1];
        }


        $processedData = [];
        $lastRecord = LivePrice::query()
            ->where('name', '!=', '0')
            ->where('form', '!=', '0')
            ->whereNotNull('min_price')
            ->whereNotNull('max_price')
            ->latest('id')
            ->first();

        if ($lastRecord != null) {

            $lastToLastDate = LivePrice::query()
                ->where('name', '!=', '0')
                ->where('form', '!=', '0')
                ->whereNotNull('min_price')
                ->whereNotNull('max_price')
                ->whereDate('created_at', '<', $lastRecord->created_at->format('Y-m-d'))
                ->latest()
                ->first();

            if ($lastToLastDate) {
                if( $startYear != '' && $endYear != '' ){
                    $today = Carbon::today();
                    if( $endYear ==  $today->year){
                        $customDate = Carbon::today()->format('Y-m-d');
                    }else{
                        if( $today->month >=1 && $today->month <= 12  ){
                            $customDate = Carbon::create($startYear, $today->month, $today->day)->format('Y-m-d');
                        }else{
                            $customDate = Carbon::create($endYear, $today->month, $today->day)->format('Y-m-d');
                        }
                    }   
                    // dd($customDate);
                    $lastEnteredRecordOfDate = LivePrice::select(DB::raw('DATE(created_at) as created_date'))
                            ->whereDate('created_at','<=', $customDate)
                            ->distinct()
                            ->orderBy('created_date', 'desc')
                            ->limit(3)
                            ->pluck('created_date');

                    // dd($lastEnteredRecordOfDate);
                    $recordDate = $customDate;
                    if( $lastEnteredRecordOfDate[0]  !=  $customDate ){
                        $recordDate = $lastEnteredRecordOfDate[0];
                        $recordDate = Carbon::create($lastEnteredRecordOfDate[0]);
                        $customDate = Carbon::create($endYear, $recordDate->month, $recordDate->day)->format('Y-m-d');

                    }
                    $data = LivePrice::query()

                        ->has('name_rel')
                        ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
                        ->with([
                            'name_rel',
                            'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
                        ])
                        ->withCount([
                            'trades as tradeCount' => function ($q) {
                                $q->whereColumn('trade_query_milestone3.qualityFormLinkWithLivePrice', 'live_prices.form');
                                // $q->whereColumn('trade_query_milestone3.qualityForm', 'live_prices.form');
                            }
                        ])
                        // ->withCount(['trades as tradeCount'])
                        ->whereNotNull('min_price')
                        ->whereNotNull('max_price')
                        ->where(['state' => $state])
                        ->where(DB::raw('date(created_at)'),  $recordDate)
                        ->get();

                    // $data = DB::table('live_prices as lp')
                    //     ->join('rice_forms as f', 'lp.form', '=', 'f.id')
                    //     ->join('rice_names as n', 'lp.name', '=', 'n.id')
                    //     ->select('lp.*', 'n.name', 'f.form_name', 
                    //         DB::raw('(SELECT MIN(min_price) FROM live_prices WHERE state = "' . $state . '") as max_price_till_now'),
                    //         DB::raw('(SELECT MAX(max_price) FROM live_prices WHERE state = "' . $state . '") as min_price_till_now')
                    //     )
                    //     ->where('f.type', $ricetype)
                    //     ->where('lp.state', $state)
                    //     ->whereDate('lp.created_at', $recordDate)
                    //     ->get();
                }else{
                    $data = LivePrice::query()
                        ->has('name_rel')
                        ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
                        ->with([
                            // 'trades',
                            'name_rel',
                            'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
                        ])
                        ->withCount([
                            'trades as tradeCount' => function ($q) {
                                $q->whereColumn('trade_query_milestone3.qualityFormLinkWithLivePrice', 'live_prices.form');
                                // $q->whereColumn('trade_query_milestone3.qualityForm', 'live_prices.form');
                            }
                        ])
                        // ->withCount(['trades as tradeCount'])
                        ->whereNotNull('min_price')
                        ->whereNotNull('max_price')
                        ->where(['state' => $state])
                        ->whereIn(DB::raw('date(created_at)'), [$lastRecord->created_at->format('Y-m-d'), $lastToLastDate->created_at->format('Y-m-d')])
                        ->get();     
                }

                foreach ($data->sortBy('name_rel.order') as $k => $v) {
                    $replaceHignfn = explode('-', $v->name_rel->type);
                    $implodeUnderscore = implode('_', $replaceHignfn);
                    $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                }

                $fiilteredProcessedData = [];
                foreach ($data->sortBy('form_rel.order') as $k => $v) {
                    $replaceHignfn = explode('-', $v->name_rel->type);
                    $implodeUnderscore = implode('_', $replaceHignfn);
                    $fiilteredProcessedData[$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                }

                foreach ($processedData as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $processedData[$k][$kk] = $fiilteredProcessedData[$kk];
                    }
                }

                // dd($lastRecord);
                if($startYear != '' && $endYear != '' ){
                    $latstRecord = Carbon::parse($recordDate)->format('Y-m-d');
                }else{
                    $latstRecord = $lastRecord->created_at->format('Y-m-d');
                }

                foreach ($processedData as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $key => $value) {
                            if (is_array($value)) {
                                foreach ($value as $ke => $val) {
                                    if (!array_key_exists($latstRecord, $val)) {
                                        unset($processedData[$k][$key][$ke]);
                                    }
                                }
                            }
                        }
                    }
                }

                foreach ($processedData as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $key => $val) {
                            if (empty($val)) {
                                unset($processedData[$k][$key]);
                            } else {
                                foreach ($val as $kk => $vv) {
                                    if ($kk != 0) {
                                        $processedData[$k][$key][$kk]['isHide'] = 'true';
                                    }
                                }
                            }
                        }
                    }
                }

                // dd($processedData);

                $newData = collect($processedData)->map(function ($item) {
                    return collect($item)->map(function ($innerItem) use ($item) {
                        $onlyValues = array_values($innerItem);
                        $onlyKeys = array_keys($innerItem);
                        foreach ($onlyValues as $k => $v) {
                            $onlyValues[$k]['is_hide'] = ($k == 0) ? 'false' : 'true';
                        }


                        $data = array_combine($onlyKeys, $onlyValues);
                        return $data;
                    });
                })->toArray();

                $order = [];
                foreach ($newData as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $order[$k][] = [$kk => $vv];
                    }
                }

                $myNewData = [];
                foreach ($order as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $newDataProcess = [];
                        foreach ($vv as $key => $value) {
                            foreach ($value as $ke => $val) {
                                $newDataProcess[] = [$ke => $val];
                            }
                            $myNewData[$k][$kk][$key] = $newDataProcess;
                        }
                    }
                }
                return response()->json([
                    'errors' => null,
                    'prices' => $myNewData,
                    'latest' => ($startYear != '' && $endYear != '')? $recordDate : $lastRecord->created_at->format('Y-m-d'),
                    'lastUpdatedDate' => $this->livePricesGlobalLastUpdatedAtFormatted(null),
                    'oldDate' => $lastToLastDate->created_at->format('Y-m-d')
                ]);
            }

            $prices = LivePrice::query()
                ->where('name', '!=', '0')
                ->where('form', '!=', '0')
                ->whereNotNull('min_price')
                ->whereNotNull('max_price')
                ->withCount(['trades as tradeCount'  => function ($q) {
                        $q->whereColumn('trade_query_milestone3.qualityFormLinkWithLivePrice', 'live_prices.form');
                        // $q->whereColumn('trade_query_milestone3.qualityForm', 'live_prices.form');
                    }])
                ->whereHas('name_rel', fn($q) => $q->where('type', $ricetype))
                ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
                ->with([
                    'name_rel' => fn($q) =>  $q->where('type', $ricetype),
                    'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
                ])
                ->where('state', $state)
                ->get();

            foreach ($prices as $k => $v) {

                $replaceHignfn = explode('-', $v->name_rel->type);
                $implodeUnderscore = implode('_', $replaceHignfn);
                $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
            }

            return response()->json([
                'errors' => null,
                'prices' => json_encode($processedData),
                'latest' => $lastRecord->created_at->format('d-M-Y, g:i A'),
                'oldDate' => ''
            ]);
        } else {

            $data = LivePrice::query()
                ->where('state', $state)
                ->whereNotNull('min_price')
                ->whereNotNull('max_price')
                ->with([
                    'name_rel',
                    'form_rel' => fn($q) => $q->orderBy('id', "ASC")->where('type', $ricetype)
                ])
                ->withCount(['trades as tradeCount'  => function ($q) {
                        $q->whereColumn('trade_query_milestone3.qualityFormLinkWithLivePrice', 'live_prices.form');
                        // $q->whereColumn('trade_query_milestone3.qualityForm', 'live_prices.form');
                    }])
                ->where(['state' => $state])
                ->where(DB::raw('date(created_at)'), now()->format('Y-m-d'))
                ->get();

            foreach ($data as $k => $v) {
                if ($v->name_rel != null && $v->state != null && $v->form_rel != null) {
                    $replaceHignfn = explode('-', $v->name_rel->type);
                    $implodeUnderscore = implode('_', $replaceHignfn);
                    $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                }
            }
            return response()->json([
                'errors' => null,
                'prices' => $processedData,
                'last_updated_record' => $lastRecord,
                'latest' => '',
                'oldDate' => ''
            ]);
        }
    }

    public function getPrices($state, $ricetype)
    {
        $cropYear = request()->get('year');
        // $today = Carbon::now();  
        // $day = $today->day;
        // $month = $today->month;
        // $year = $cropYear;


        // $date = Carbon::create($year, $month, $day);


        $processedData = [];

        $livePriceBaseQuery = fn () => LivePrice::query()
            ->where('name', '!=', '0')
            ->where('form', '!=', '0')
            ->whereNotNull('min_price')
            ->whereNotNull('max_price')
            ->where('state', $state)
            ->when($cropYear, fn ($q) => $q->where('cropYear', $cropYear));

        $invalidLatestTupleKeys = $this->invalidLatestLivePriceTupleKeys($state, $cropYear);

        // Latest activity for this state/crop year (admin saves touch updated_at).
        $lastRecord = $livePriceBaseQuery()
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($lastRecord) {
            $latestDate = Carbon::parse($lastRecord->updated_at)->format('Y-m-d');
            $latestStart = Carbon::parse($latestDate)->startOfDay();
            $latestEnd = Carbon::parse($latestDate)->endOfDay();

            $previousDate = $livePriceBaseQuery()
                ->where('updated_at', '<', $latestStart)
                ->max(DB::raw('DATE(updated_at)'));

            $closingMap = $this->latestLivePriceClosingMap($state, $cropYear);

            $data = LivePrice::query()
                    ->with([
                        'name_rel:id,name,type,order',
                        'form_rel:id,form_name,type,order,status',
                    ])
                    ->join('rice_names as rn', 'rn.id', '=', 'live_prices.name')
                    ->join('rice_forms as rf', 'rf.id', '=', 'live_prices.form')
                    ->select('live_prices.*')
                    ->whereNotNull('live_prices.min_price')
                    ->whereNotNull('live_prices.max_price')
                    ->where('live_prices.state', $state)
                    ->when($cropYear, fn ($q) => $q->where('live_prices.cropYear', $cropYear))
                    ->where('rn.type', $ricetype)
                    ->where('rf.type', $ricetype)
                    ->where('rf.status', 1)
                    ->where(function ($q) use ($latestStart, $latestEnd, $previousDate) {
                        $q->whereBetween('live_prices.updated_at', [$latestStart, $latestEnd]);
                        if ($previousDate) {
                            $prevStart = Carbon::parse($previousDate)->startOfDay();
                            $prevEnd = Carbon::parse($previousDate)->endOfDay();
                            $q->orWhereBetween('live_prices.updated_at', [$prevStart, $prevEnd]);
                        }
                    })
                    ->orderBy('live_prices.updated_at', 'desc')
                    ->get();

            $data->each(function ($row) use ($closingMap) {
                $row->closing_data = $closingMap[$this->livePriceTupleKey($row)] ?? null;
            });

                // Same-day re-saves: keep the row with the latest updated_at per name+form.
                $data = $data
                    ->groupBy(fn ($row) => (string) $row->name.'_'.(string) $row->form)
                    ->map(fn ($rows) => $rows->sortByDesc('id')->first())
                    ->filter(function ($row) use ($invalidLatestTupleKeys) {
                        return ! isset($invalidLatestTupleKeys[$this->livePriceTupleKey($row)])
                            && $this->hasUsableLivePrice($row);
                    })
                    ->values();

                foreach ($data->sortBy('name_rel.order') as $v) {
                    $replaceHignfn = explode('-', $v->name_rel->type);
                    $implodeUnderscore = implode('_', $replaceHignfn);
                    $priceDate = Carbon::parse($v->updated_at)->format('Y-m-d');
                    $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$priceDate] = $v;
                }

                $fiilteredProcessedData = [];
                foreach ($data->sortBy('form_rel.order') as $v) {
                    $replaceHignfn = explode('-', $v->name_rel->type);
                    $implodeUnderscore = implode('_', $replaceHignfn);
                    $priceDate = Carbon::parse($v->updated_at)->format('Y-m-d');
                    $fiilteredProcessedData[$v->name_rel->name][$v->form_rel->form_name][$priceDate] = $v;
                }

                foreach ($processedData as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $processedData[$k][$kk] = $fiilteredProcessedData[$kk];
                    }
                }

                $latstRecord = $latestDate;

                foreach ($processedData as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $key => $value) {
                            if (is_array($value)) {
                                foreach ($value as $ke => $val) {
                                    if (!array_key_exists($latstRecord, $val)) {
                                        unset($processedData[$k][$key][$ke]);
                                    }
                                }
                            }
                        }
                    }
                }

                foreach ($processedData as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $key => $val) {
                            if (empty($val)) {
                                unset($processedData[$k][$key]);
                            } else {
                                foreach ($val as $kk => $vv) {
                                    if ($kk != 0) {
                                        $processedData[$k][$key][$kk]['isHide'] = 'true';
                                    }
                                }
                            }
                        }
                    }
                }

                $newData = collect($processedData)->map(function ($item) {
                    return collect($item)->map(function ($innerItem) use ($item) {
                        $onlyValues = array_values($innerItem);
                        $onlyKeys = array_keys($innerItem);
                        foreach ($onlyValues as $k => $v) {
                            $onlyValues[$k]['is_hide'] = ($k == 0) ? 'false' : 'true';
                        }
                        $data = array_combine($onlyKeys, $onlyValues);
                        return $data;
                    });
                })->toArray();

                $order = [];
                foreach ($newData as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $order[$k][] = [$kk => $vv];
                    }
                }

                $myNewData = [];
                foreach ($order as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $newDataProcess = [];
                        foreach ($vv as $key => $value) {
                            foreach ($value as $ke => $val) {
                                $newDataProcess[] = [$ke => $val];
                            }
                            $myNewData[$k][$kk][$key] = $newDataProcess;
                        }
                    }
                }

            return response()->json([
                'errors' => null,
                'prices' => $myNewData,
                'latest' => $latestDate,
                'lastUpdatedDate' => $this->livePricesGlobalLastUpdatedAtFormatted($cropYear),
                'oldDate' => $previousDate ? Carbon::parse($previousDate)->format('Y-m-d') : '',
            ]);
        }

        // if no records found
        return response()->json([
            'errors' => null,
            'prices' => [],
            'latest' => '',
            'oldDate' => ''
        ]);
    }

   
    // public function getPricesWeb(Request $request ,$state, $ricetype)
    // {
    //     $cropYear = request()->get('year');

    //     $todayDate = Carbon::now();
        
    //     if( $cropYear ){
    //         $year = $request->year;
    //         $date = $todayDate->day;
    //         $month = $todayDate->month;


    //     }else{
    //         $year = $todayDate->year;
    //         $date = $todayDate->day;
    //         $month = $todayDate->month;

    //     }


    //     $lastEnteredRecord = Carbon::createFromDate($year, $month, $date)->format('Y-m-d');
    //     $currentYear = $todayDate->year;

    //     $processedData = [];
    //     $livePricesClosingOpening = [];

    //     $hasClosingName = [];
    //     $hasClosingForm = [];
    //     $hasOpenigClosingConcade = [];

    //     $lastRecord = LivePrice::query()
    //             ->where('name', '!=', '0')
    //             ->where('form', '!=', '0')
    //             ->whereNotNull('min_price')
    //             ->whereNotNull('max_price')
    //             ->where('state', $state)
    //             ->whereDate('created_at' , $lastEnteredRecord)
    //             // ->where('cropYear' , $cropYear)
    //             ->latest('id')
    //             ->first();

    //     if( $cropYear == $currentYear ){
    //         $data = LivePrice::query()
    //                 ->has('name_rel')
    //                 ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
    //                 ->with([
    //                     'name_rel',
    //                     'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
    //                 ])
    //                 ->withCount([
    //                     'trades as tradeCount' => function ($q) {
    //                         $q->whereColumn('trade_query_milestone3.qualityForm', 'live_prices.form');
    //                     }
    //                 ])
    //                 ->whereNotNull('min_price')
    //                 ->whereNotNull('max_price')
    //                 ->where('state', $state)
    //                 ->whereDate('created_at',$lastEnteredRecord)->get();

    //     }elseif( ($cropYear+1) == $currentYear ){

    //         $livePricesClosingOpening = LivePricesOpeningClosing::select(["id","trade_for","farming_type","name","form","cropYear","state","opening","closing"])
    //             ->where('state', $state)
    //             ->where('cropYear', $cropYear)
    //             ->where(function ($q) {
    //                 $q->whereNotNull('closing')->where('closing', '!=', '');
    //             })
    //             ->whereHas('name_rel', fn($q) => $q->where('type', $ricetype))
    //             // ->whereHas('form_rel')
    //             ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
    //             ->with([
    //                 'name_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC"),
    //                 // 'form_rel'
    //                 'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
    //             ])
    //             ->get();

    //             $hasClosingName = $livePricesClosingOpening->pluck('name');
    //             $hasClosingForm = $livePricesClosingOpening->pluck('form');

    //             $hasOpenigClosingConcade = [];
    //             foreach ($hasClosingName as $index => $key) {
    //                 $hasOpenigClosingConcade[] = strtolower($key . '_' . $hasClosingForm[$index]);
    //             }


    //             $data = LivePrice::query()
    //                 ->has('name_rel')
    //                 ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
    //                 ->with([
    //                     'name_rel',
    //                     'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
    //                 ])
    //                 ->withCount([
    //                     'trades as tradeCount' => function ($q) {
    //                         $q->whereColumn('trade_query_milestone3.qualityForm', 'live_prices.form');
    //                     }
    //                 ])
    //                 ->whereNotNull('min_price')
    //                 ->whereNotNull('max_price')
    //                 ->where('state', $state)
    //                 ->where('cropYear' , $cropYear)
    //                 ->where(function ($q) {
    //                         $q->whereNull('closing')->orWhere('closing', '');
    //                     })
    //                 ->whereDate('created_at',$lastEnteredRecord)->get();

    //                 if($data->count() == 0){
    //                     $data = LivePrice::query()
    //                         ->has('name_rel')
    //                         ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
    //                         ->with([
    //                             'name_rel',
    //                             'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
    //                         ])
    //                         ->withCount([
    //                             'trades as tradeCount' => function ($q) {
    //                                 $q->whereColumn('trade_query_milestone3.qualityForm', 'live_prices.form');
    //                             }
    //                         ])
    //                         ->where('cropYear' , $cropYear)
    //                         ->whereNotNull('min_price')
    //                         ->whereNotNull('max_price')
    //                         ->where('state', $state)
    //                         ->where(function ($q) {
    //                             $q->whereNull('closing')->orWhere('closing', '');
    //                         })
    //                         ->whereDate('created_at','<',$lastEnteredRecord)->first();
    //                 }

                    
    //                 // ->groupBy('name_rel.name')->toArray();

    //     }else{
    //         $date = Carbon::create($cropYear, $month, $date);

    //         // $hasData = LivePrice::query()
    //         //         ->has('name_rel')
    //         //         ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
    //         //         ->with([
    //         //             'name_rel',
    //         //             'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
    //         //         ])
    //         //         ->withCount([
    //         //             'trades as tradeCount' => function ($q) {
    //         //                 $q->whereColumn('trade_query_milestone3.qualityForm', 'live_prices.form');
    //         //             }
    //         //         ])
    //         //         ->whereNotNull('min_price')
    //         //         ->whereNotNull('max_price')
    //         //         ->where('state', $state)
    //         //         ->where('cropYear', $cropYear)
    //         //         ->whereDate('created_at','<=', $date)->orderBy('id' , 'DESC')->first();

    //         $livePricesClosingOpening = LivePricesOpeningClosing::select(["id","trade_for","farming_type","name","form","cropYear","state","opening","closing"])
    //                 ->where('cropYear' , $cropYear)
    //                 ->where('state', $state)
    //                 ->where(function ($q) {
    //                     $q->whereNotNull('closing')->where('closing', '!=', '');
    //                 })
    //                 ->whereHas('name_rel', fn($q) => $q->where('type', $ricetype))
    //                 // ->whereHas('form_rel')
    //                 ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
    //                 ->with([
    //                     'name_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC"),
    //                     // 'form_rel'
    //                     'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
    //                 ])
    //                 ->get();


    //         $hasClosingName = $livePricesClosingOpening->pluck('name');
    //         $hasClosingForm = $livePricesClosingOpening->pluck('form');

    //         $hasOpenigClosingConcade = [];
    //         foreach ($hasClosingName as $index => $key) {
    //             $hasOpenigClosingConcade[] = strtolower($key . '_' . $hasClosingForm[$index]);
    //         }

    //         $hasData = LivePrice::where('state', $state)
    //                 ->where('cropYear', $cropYear)
    //                 ->whereDate('created_at',$date)->first();

    //         if( !$hasData ){
    //             $lastAddedDate = LivePrice::where('state', $state)
    //                 ->where('cropYear', $cropYear)
    //                 ->whereDate('created_at','<', $date)->orderBy('created_at' , 'desc')->first()->created_at;
    //             $date = Carbon::create($lastAddedDate);
    //         }
            
    //         $data = LivePrice::query()
    //                 ->has('name_rel')
    //                 ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
    //                 ->with([
    //                     'name_rel',
    //                     'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
    //                 ])
    //                 ->withCount([
    //                     'trades as tradeCount' => function ($q) {
    //                         $q->whereColumn('trade_query_milestone3.qualityForm', 'live_prices.form');
    //                     }
    //                 ])
    //                 ->where(function ($q) {
    //                     $q->whereNotNull('closing')->where('closing', '!=', '');
    //                 })
    //                 ->whereNotNull('min_price')
    //                 ->whereNotNull('max_price')
    //                 ->where('state', $state)
    //                 ->where('cropYear', $cropYear)
    //                 ->whereDate('created_at',$date)->get();
    //                 $lastRecord = (count($data) > 0) ? $data[0] : collect();
    //     }

     
    //     if (!$data->isEmpty()) {
    //         foreach ($data->sortBy('name_rel.order') as $v) {
    //             if ( count($hasOpenigClosingConcade) > 0 ){
    //                 $combineNameForm = $v->name.'_'.$v->form;

    //                 if( !in_array($combineNameForm, $hasOpenigClosingConcade) ) {
    //                     $replaceHignfn = explode('-', $v->name_rel->type);
    //                     $implodeUnderscore = implode('_', $replaceHignfn);
    //                     $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
    //                 }
    //             }else{
    //                 $replaceHignfn = explode('-', $v->name_rel->type);
    //                 $implodeUnderscore = implode('_', $replaceHignfn);
    //                 $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
    //             }
    //         }

    //         $fiilteredProcessedData = [];
    //         foreach ($data->sortBy('form_rel.order') as $v) {

    //             if ( count($hasOpenigClosingConcade) > 0 ){
    //                 $combineNameForm = $v->name.'_'.$v->form;

    //                 if( !in_array($combineNameForm, $hasOpenigClosingConcade) ) {
    //                     $replaceHignfn = explode('-', $v->name_rel->type);
    //                     $implodeUnderscore = implode('_', $replaceHignfn);
    //                     $fiilteredProcessedData[$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
    //                 }
    //             }else{
    //                 $replaceHignfn = explode('-', $v->name_rel->type);
    //                 $implodeUnderscore = implode('_', $replaceHignfn);
    //                 $fiilteredProcessedData[$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
    //             }
                
    //         }
    //         foreach ($processedData as $k => $v) {
    //             foreach ($v as $kk => $vv) {
    //                 $processedData[$k][$kk] = $fiilteredProcessedData[$kk];
    //             }
    //         }

    //         $latstRecord = ($lastRecord) ? $lastRecord->created_at->format('Y-m-d') : 0;
    //         foreach ($processedData as $k => $v) {
    //             if (is_array($v)) {
    //                 foreach ($v as $key => $value) {
    //                     if (is_array($value)) {
    //                         foreach ($value as $ke => $val) {
    //                             if (!array_key_exists($latstRecord, $val)) {
    //                                 unset($processedData[$k][$key][$ke]);
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //         }

    //         foreach ($processedData as $k => $v) {
    //             if (is_array($v)) {
    //                 foreach ($v as $key => $val) {
    //                     if (empty($val)) {
    //                         unset($processedData[$k][$key]);
    //                     } else {
    //                         foreach ($val as $kk => $vv) {
    //                             if ($kk != 0) {
    //                                 $processedData[$k][$key][$kk]['isHide'] = 'true';
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //         }

    //         $newData = collect($processedData)->map(function ($item) {
    //             return collect($item)->map(function ($innerItem) use ($item) {
    //                 $onlyValues = array_values($innerItem);
    //                 $onlyKeys = array_keys($innerItem);
    //                 foreach ($onlyValues as $k => $v) {
    //                     $onlyValues[$k]['is_hide'] = ($k == 0) ? 'false' : 'true';
    //                 }
    //                 $data = array_combine($onlyKeys, $onlyValues);
    //                 return $data;
    //             });
    //         })->toArray();

    //         $order = [];
    //         foreach ($newData as $k => $v) {
    //             foreach ($v as $kk => $vv) {
    //                 $order[$k][] = [$kk => $vv];
    //             }
    //         }

    //         $myNewData = [];

    //         foreach ($order as $k => $v) {
    //             foreach ($v as $kk => $vv) {
    //                 foreach ($vv as $key => $value) {

    //                     $newDataProcess = [];

    //                     foreach ($value as $ke => $val) {
    //                         if (!str_contains(strtolower($ke), 'new') && $cropYear == "2023") {
    //                             $newDataProcess[] = [$ke => $val];
    //                         }else{
    //                             $newDataProcess[] = [$ke => $val];
    //                         }
    //                     }

    //                     if (!empty($newDataProcess)) {
    //                         $myNewData[$k][$kk][$key] = $newDataProcess;
    //                     }
    //                 }
    //             }
    //         }
    //         // dd($myNewData);
    //         $latestEnteredRecord = LivePrice::orderBy('id', 'desc')->first();
            
    //         return response()->json([
    //             'errors' => null,
    //             'prices' => $myNewData,
    //             'closing' => [$ricetype => $livePricesClosingOpening],
    //             'latest' => $latstRecord,
    //             'lastUpdatedDate' => ($latestEnteredRecord->updated_at) ? $latestEnteredRecord->updated_at->format('d-M-Y, g:i A') : '',
    //             // 'oldDate' => $lastToLastDate
    //         ]);
    //     }else{
    //         return response()->json([
    //             'errors' => null,
    //             'prices' => [],
    //             'closing' => [$ricetype => $livePricesClosingOpening],
    //             'latest' => '',
    //             'lastUpdatedDate' => '',
    //             // 'oldDate' => $lastToLastDate
    //         ]);
    //     }

        


    // }



    //6 feb 2026 change by sandeep for form_order sort
    // public function getPricesWeb(Request $request ,$state, $ricetype)
    // {
    //     $LivePriceStatusMessage = LivePriceStatusMessage::orderBy('id' , 'desc')->first();
    //     $latestCropYearRecord = LivePrice::orderBy('cropYear' , 'desc')->first();
    //     $latestCropYear = $latestCropYearRecord->cropYear;

    //     $states = [];
    //     $todayDate = Carbon::now();
    //     $cropYear = (request()->has('year')) ? request()->get('year') : $latestCropYear;

    //     $year = ( $todayDate->year >= $latestCropYear) ? $todayDate->year : $cropYear ;
    //     $date = $todayDate->day;
    //     $month = $todayDate->month;

    //     // $todayDate = Carbon::now();

    //     // $cropYear = (request()->has('year')) ? request()->get('year') : $today->year;
    //     // $year = $cropYear;
    //     // $date = $todayDate->day;
    //     // $month = $todayDate->month;

    //     $lastEnteredRecord = Carbon::createFromDate($year, $month, $date)->format('Y-m-d');

    //     $lastRecord = LivePrice::query()
    //             ->where('name', '!=', '0')
    //             ->where('form', '!=', '0')
    //             ->whereNotNull('min_price')
    //             ->whereNotNull('max_price')
    //             ->where('state', $state)
    //             ->whereDate('created_at' , $lastEnteredRecord)
    //             ->where('cropYear' , $cropYear)
    //             ->latest('id');

    //     if( !$lastRecord->exists() ){
    //         $lastRecord = LivePrice::query()
    //             ->where('name', '!=', '0')
    //             ->where('form', '!=', '0')
    //             ->whereNotNull('min_price')
    //             ->whereNotNull('max_price')
    //             ->where('state', $state)
    //             ->where('cropYear' , $cropYear)
    //             ->whereDate('created_at' ,'<', $lastEnteredRecord)
    //             ->latest('id');

    //     }

    //     $lastEnteredRecord = $lastRecord->first();

    //     $data = LivePrice::query()
    //             ->has('name_rel')
    //             ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype)->orderBy('order', "ASC"))
    //             ->with([
    //                 'name_rel',
    //                 'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('order', "ASC")
    //                 // 'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
    //             ])
    //             ->withCount([
    //                 'trades as tradeCount' => function ($q) {
    //                     $q->whereColumn('trade_query_milestone3.qualityFormLinkWithLivePrice', 'live_prices.form')->whereColumn('trade_query_milestone3.stateLinkWithLivePrice' , 'live_prices.state');
    //                     // $q->whereColumn('trade_query_milestone3.qualityForm', 'live_prices.form');
    //                 }
    //             ])
    //             ->whereNotNull('min_price')
    //             ->whereNotNull('max_price')
    //             ->where('state', $state)
    //             ->where('cropYear' , $cropYear)
    //             ->orderBy('name_order')
    //             ->orderBy('form_order')
    //             ->whereDate('created_at',$lastEnteredRecord->created_at)->get();


    //             $latestIds = LivePricesOpeningClosing::selectRaw('MAX(id) as id')
    //                 ->where('cropYear', $cropYear)
    //                 ->where('state', $state)
    //                 ->whereNotNull('closing')
    //                 ->where('closing', '!=', '')
    //                 ->groupBy('name', 'form', 'cropYear', 'state');

    //             $livePricesClosingOpening = LivePricesOpeningClosing::select([
    //                     "id",
    //                     "trade_for",
    //                     "farming_type",
    //                     "name",
    //                     "form",
    //                     "cropYear",
    //                     "state",
    //                     "opening",
    //                     "closing"
    //                 ])
    //                 ->whereIn('id', $latestIds)
    //                 ->whereHas('name_rel', fn ($q) => $q->where('type', $ricetype))
    //                 ->whereHas('form_rel', fn ($q) => $q->where('type', $ricetype)->orderBy('order', "ASC"))
    //                 ->with([
    //                     'name_rel' => fn ($q) => $q->where('type', $ricetype)->orderBy('id', 'ASC'),
    //                     'form_rel' => fn ($q) => $q->where('type', $ricetype)->orderBy('order', 'ASC'),
    //                     // 'form_rel' => fn ($q) => $q->where('type', $ricetype)->orderBy('id', 'ASC'),
    //                 ])
    //                 ->orderBy('id', 'DESC')
    //                 ->get(); 
    //             // $livePricesClosingOpening = LivePricesOpeningClosing::select(["id","trade_for","farming_type","name","form","cropYear","state","opening","closing"])
    //             //     ->where('cropYear' , $cropYear)
    //             //     ->where('state', $state)
    //             //     ->where(function ($q) {
    //             //         $q->whereNotNull('closing')->where('closing', '!=', '');
    //             //     })
    //             //     ->whereHas('name_rel', fn($q) => $q->where('type', $ricetype))
    //             //     // ->whereHas('form_rel')
    //             //     ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
    //             //     ->with([
    //             //         'name_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC"),
    //             //         // 'form_rel'
    //             //         'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
    //             //     ])
    //             //     ->get();

    //             $hasClosingName = $livePricesClosingOpening->pluck('name');
    //             $hasClosingForm = $livePricesClosingOpening->pluck('form');

    //             $hasOpenigClosingConcade = [];
    //             foreach ($hasClosingName as $index => $key) {
    //                 $hasOpenigClosingConcade[] = strtolower($key . '_' . $hasClosingForm[$index]);
    //             }

    //         $processedData = [];
    //         $temp = [];

    //         if( $cropYear == 2023 ){
    //             // foreach ($data as $k => $val) {
    //             //     if (!str_contains(strtolower($val['form_rel']['form_name']), 'new') && $cropYear == "2023") {
    //             //         $riceNameForm[] = $val->name.'_'.$val->form;
    //             //         $states[] = $val['state'];
    //             //     }
    //             // }


    //             foreach ($data as $v) {
    //                 if (!str_contains(strtolower($v['form_rel']['form_name']), 'new crop') && $cropYear == "2023") {
    //                     if ( count($hasOpenigClosingConcade) > 0 ){
    //                         $combineNameForm = $v->name.'_'.$v->form;
    //                         if( !in_array($combineNameForm, $hasOpenigClosingConcade) ) {
    //                             $riceType = $v->name_rel->name;      
    //                             $formName = $v->form_rel->form_name; 
    //                             $date     = Carbon::parse($v->created_at)->format('Y-m-d');

    //                             $replaceHignfn = explode('-', $v->name_rel->type);
    //                             $implodeUnderscore = implode('_', $replaceHignfn);
    //                             $temp[$implodeUnderscore][$riceType][$formName][$date] = $v;
    //                         }
    //                     }else{
    //                         $riceType = $v->name_rel->name;      
    //                         $formName = $v->form_rel->form_name; 
    //                         $date     = Carbon::parse($v->created_at)->format('Y-m-d');

    //                         $replaceHignfn = explode('-', $v->name_rel->type);
    //                         $implodeUnderscore = implode('_', $replaceHignfn);
    //                         $temp[$implodeUnderscore][$riceType][$formName][$date] = $v;
    //                     }
    //                 }
    //             }
    //         }else{
    //             // dd($data->toArray());
    //             foreach ($data as $v) {
    //                 if ( count($hasOpenigClosingConcade) > 0 ){
    //                     $combineNameForm = $v->name.'_'.$v->form;
    //                     if( !in_array($combineNameForm, $hasOpenigClosingConcade) ) {
    //                         $riceType = $v->name_rel->name;      
    //                         $formName = $v->form_rel->form_name; 
    //                         $date     = Carbon::parse($v->created_at)->format('Y-m-d');
                            
    //                         $replaceHignfn = explode('-', $v->name_rel->type);
    //                         $implodeUnderscore = implode('_', $replaceHignfn);

    //                         $temp[$implodeUnderscore][$riceType][$formName][$date] = $v;
    //                     }
    //                 }else{

    //                     $riceType = $v->name_rel->name;      
    //                     $formName = $v->form_rel->form_name; 
    //                     $date     = Carbon::parse($v->created_at)->format('Y-m-d');

    //                     $replaceHignfn = explode('-', $v->name_rel->type);
    //                     $implodeUnderscore = implode('_', $replaceHignfn);

    //                     $temp[$implodeUnderscore][$riceType][$formName][$date] = $v;
    //                 }
    //             }
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Convert temp structure to REQUIRED final response
    //         |--------------------------------------------------------------------------
    //         */
    //         $replaceHignfn = explode('-', $ricetype);
    //         $implodeUnderscore = implode('_', $replaceHignfn);

    //         if( array_key_exists($implodeUnderscore , $temp) ){
    //             foreach ($temp[$implodeUnderscore] as $riceType => $forms) {

    //                 $formArray = [];

    //                 foreach ($forms as $formName => $dates) {
    //                     $formArray[] = [
    //                         $formName => $dates
    //                     ];
    //                 }

    //                 $processedData[$implodeUnderscore][] = [
    //                     $riceType => $formArray
    //                 ];
    //             }
    //         }
            
    //     // $lastToLastDate = LivePrice::where('name', '!=', '0')->where('form', '!=', '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('created_at', 'DESC')
    //     //         ->whereDate('created_at', '<', $lastRecord->first()->created_at->format('Y-m-d'))->get();
    //     $latestEnteredRecord = LivePrice::orderBy('id', 'desc')->first();
    //     return response()->json([
    //             'errors' => null,
    //             'livePriceStatusMessage' => $LivePriceStatusMessage,
    //             'prices' => $processedData,
    //             'closing' => [$ricetype => $livePricesClosingOpening],
    //             'latest' => Carbon::parse($lastEnteredRecord->created_at)->format('Y-m-d'),
    //             'lastUpdatedDate' => ($latestEnteredRecord->updated_at)? $latestEnteredRecord->updated_at->format('d-M-Y, g:i A') : '',
    //             // 'oldDate' => $lastToLastDate[0]->created_at->format('Y-m-d')
    //         ]);
    // }
    //6 feb 2026 change by sandeep for form_order sort

    public function getPricesWeb(Request $request ,$state, $ricetype)
    {
        $LivePriceStatusMessage = LivePriceStatusMessage::orderBy('id' , 'desc')->first();
        $todayDate = Carbon::now();
        $latestCropYear = (int) (LivePrice::max('cropYear') ?: $todayDate->year);

        $cropYear = (request()->has('year')) ? request()->get('year') : $latestCropYear;

        $year = ($todayDate->year >= $latestCropYear) ? $todayDate->year : $cropYear;
        $date = $todayDate->day;
        $month = $todayDate->month;

        $lastEnteredRecord = Carbon::createFromDate($year, $month, $date)->format('Y-m-d');
        $lastEnteredStart = Carbon::parse($lastEnteredRecord)->startOfDay();
        $lastEnteredEnd = Carbon::parse($lastEnteredRecord)->endOfDay();

        $lastRecord = LivePrice::query()
            ->where('name', '!=', '0')
            ->where('form', '!=', '0')
            ->whereNotNull('min_price')
            ->whereNotNull('max_price')
            ->where('min_price', '>', 0)
            ->where('max_price', '>', 0)
            ->where('state', $state)
            ->whereBetween('created_at', [$lastEnteredStart, $lastEnteredEnd])
            ->where('cropYear' , $cropYear)
            ->latest('id')
            ->first();

        if(!$lastRecord){
            $lastRecord = LivePrice::query()
                ->where('name', '!=', '0')
                ->where('form', '!=', '0')
                ->whereNotNull('min_price')
                ->whereNotNull('max_price')
                ->where('min_price', '>', 0)
                ->where('max_price', '>', 0)
                ->where('state', $state)
                ->where('cropYear' , $cropYear)
                ->where('created_at' ,'<', $lastEnteredStart)
                ->latest('id')
                ->first();
        }

        $lastEnteredRecord = $lastRecord;

        if (!$lastEnteredRecord) {
            $latestForMeta = LivePrice::query()
                ->where('state', $state)
                ->where('cropYear', $cropYear)
                ->orderBy('updated_at', 'desc')
                ->first()
                ?: LivePrice::orderBy('updated_at', 'desc')->first();

            return response()->json([
                'errors' => null,
                'livePriceStatusMessage' => $LivePriceStatusMessage,
                'prices' => [],
                'closing' => [$ricetype => []],
                'latest' => $latestForMeta
                    ? Carbon::parse($latestForMeta->created_at)->format('Y-m-d')
                    : $todayDate->format('Y-m-d'),
                'lastUpdatedDate' => $this->livePricesGlobalLastUpdatedAtFormatted($cropYear),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | MAIN DATA QUERY
        |--------------------------------------------------------------------------
        */

        $invalidLatestTupleKeys = $this->invalidLatestLivePriceTupleKeys($state, $cropYear);

        $priceDayStart = Carbon::parse($lastEnteredRecord->created_at)->startOfDay();
        $priceDayEnd = Carbon::parse($lastEnteredRecord->created_at)->endOfDay();
        $latestPriceIdsForDate = LivePrice::query()
            ->selectRaw('MAX(id) as id')
            ->where('name', '!=', '0')
            ->where('form', '!=', '0')
            ->where('state', $state)
            ->where('cropYear', $cropYear)
            ->whereBetween('created_at', [$priceDayStart, $priceDayEnd])
            ->groupBy('name', 'form', 'state', 'cropYear');

        $data = LivePrice::query()
            ->with([
                'name_rel:id,name,type,order',
                'form_rel:id,form_name,type,order,status',
            ])
            ->join('rice_names as rn', 'rn.id', '=', 'live_prices.name')
            ->join('rice_forms as rf', 'rf.id', '=', 'live_prices.form')
            ->select('live_prices.*')
            ->withCount([
                'trades as tradeCount' => function ($q) {
                    $q->whereColumn('trade_query_milestone3.qualityFormLinkWithLivePrice', 'live_prices.form')
                      ->whereColumn('trade_query_milestone3.stateLinkWithLivePrice' , 'live_prices.state');
                }
            ])
            ->whereIn('live_prices.id', $latestPriceIdsForDate)
            ->where('live_prices.state', $state)
            ->where('live_prices.cropYear' , $cropYear)
            ->where('rn.type', $ricetype)
            ->where('rf.type', $ricetype)
            ->where('rf.status', 1)
            ->orderByRaw('ISNULL(rn.order) ASC, rn.order ASC')
            ->orderByRaw('ISNULL(rf.order) ASC, rf.order ASC')
            ->get();

        $data = $data
            ->filter(function ($row) use ($invalidLatestTupleKeys) {
                return ! isset($invalidLatestTupleKeys[$this->livePriceTupleKey($row)])
                    && $this->hasUsableLivePrice($row);
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | ✅ EXTRA SORT (name_rel.order + form_rel.order) — nulls last
        |--------------------------------------------------------------------------
        */

        $data = $data->sortBy([
            [fn($x) => $x->name_rel->order ?? 999, 'asc'],
            [fn($x) => $x->form_rel->order ?? 999, 'asc'],
        ])->values();

        /*
        |--------------------------------------------------------------------------
        | OPENING / CLOSING (UNCHANGED)
        |--------------------------------------------------------------------------
        */

        $latestIds = LivePricesOpeningClosing::selectRaw('MAX(id) as id')
            ->where('cropYear', $cropYear)
            ->where('state', $state)
            ->whereNotNull('closing')
            ->where('closing', '!=', '')
            ->groupBy('name', 'form', 'cropYear', 'state');

            $livePricesClosingOpening = LivePricesOpeningClosing::query()
                ->select('live_price_closing.*')
                ->join('rice_names as rn', 'rn.id', '=', 'live_price_closing.name')
                ->join('rice_forms as rf', 'rf.id', '=', 'live_price_closing.form')

                ->whereIn('live_price_closing.id', $latestIds)
                ->where('rn.type', $ricetype)
                ->where('rf.type', $ricetype)

                /* 2️⃣ Name order */
                ->orderBy('rn.order', 'ASC')

                /* 3️⃣ Form order */
                ->orderBy('rf.order', 'ASC')

                /* 4️⃣ Optional fallback */
                ->orderBy('live_price_closing.id', 'DESC')

                ->with([
                    'name_rel:id,name,order,type',
                    'form_rel:id,form_name,order,type'
                ])
                ->get();

        // $livePricesClosingOpening = LivePricesOpeningClosing::select([
        //         "id","trade_for","farming_type","name","form","cropYear","state","opening","closing"
        //     ])
        //     ->whereIn('id', $latestIds)
        //     ->whereHas('name_rel', fn ($q) => $q->where('type', $ricetype))
        //     ->whereHas('form_rel', fn ($q) => $q->where('type', $ricetype)->orderBy('order', "ASC"))
        //     ->with([
        //         'name_rel' => fn ($q) => $q->where('type', $ricetype)->orderBy('id', 'ASC'),
        //         'form_rel' => fn ($q) => $q->where('type', $ricetype)->orderBy('order', 'ASC'),
        //     ])
        //     ->orderBy('id', 'DESC')
        //     ->get();

        /*
        |--------------------------------------------------------------------------
        | ✅ SORT CLOSING BY form_order
        |--------------------------------------------------------------------------
        */

        $livePricesClosingOpening = $livePricesClosingOpening
            ->sortBy(fn ($row) => $row->form_order ?? 999)
            ->values();

            

        $hasClosingName = $livePricesClosingOpening->pluck('name');
        $hasClosingForm = $livePricesClosingOpening->pluck('form');

        $hasOpenigClosingConcade = [];

        foreach ($hasClosingName as $index => $key) {
            $hasOpenigClosingConcade[] = strtolower($key . '_' . $hasClosingForm[$index]);
        }

        /*
        |--------------------------------------------------------------------------
        | EXISTING TEMP PROCESSING (UNCHANGED)
        |--------------------------------------------------------------------------
        */

        $temp = [];

        foreach ($data as $v) {

            if(count($hasOpenigClosingConcade) > 0){
                $combineNameForm = $v->name.'_'.$v->form;
                if(in_array($combineNameForm,$hasOpenigClosingConcade)) continue;
            }

            // If created_at or updated_at is not today, treat as not updated 
            $todayStr = $todayDate->format('Y-m-d');
            $createdNotToday = $v->created_at ? $v->created_at->format('Y-m-d') !== $todayStr : true;
            $updatedNotToday = $v->updated_at ? $v->updated_at->format('Y-m-d') !== $todayStr : true;
            if ($createdNotToday || $updatedNotToday) {
                $v->is_updated_by_admin = 0;
            }

            $riceType = $v->name_rel->name;
            $formName = $v->form_rel->form_name;
            $date     = Carbon::parse($v->created_at)->format('Y-m-d');

            $replaceHignfn = explode('-', $v->name_rel->type);
            $implodeUnderscore = implode('_', $replaceHignfn);

            $temp[$implodeUnderscore][$riceType][$formName][$date] = $v;
        }

        /*
        |--------------------------------------------------------------------------
        | FINAL RESPONSE FORMAT (UNCHANGED)
        |--------------------------------------------------------------------------
        */

        $processedData = [];
        $replaceHignfn = explode('-', $ricetype);
        $implodeUnderscore = implode('_', $replaceHignfn);

        if(array_key_exists($implodeUnderscore , $temp)){

            // Build fast lookup maps using rice_names.order and rice_forms.order
            $nameOrderMap = [];
            $formOrderMap = [];
            foreach ($data as $row) {
                if ($row->name_rel && isset($row->name_rel->name)) {
                    $nameOrderMap[$row->name_rel->name] = (int) ($row->name_rel->order ?? 999);
                }
                if ($row->form_rel && isset($row->form_rel->form_name)) {
                    $formOrderMap[$row->form_rel->form_name] = (int) ($row->form_rel->order ?? 999);
                }
            }

            // Sort outer keys (rice names) by numeric name_order
            uksort($temp[$implodeUnderscore], function ($a, $b) use ($nameOrderMap) {
                $aOrder = $nameOrderMap[$a] ?? 999;
                $bOrder = $nameOrderMap[$b] ?? 999;
                return $aOrder <=> $bOrder;
            });

            foreach ($temp[$implodeUnderscore] as $riceType => $forms) {

                // Sort inner keys (form names) by numeric form_order
                uksort($forms, function ($a, $b) use ($formOrderMap) {
                    $aOrder = $formOrderMap[$a] ?? 999;
                    $bOrder = $formOrderMap[$b] ?? 999;
                    return $aOrder <=> $bOrder;
                });

                $formArray = [];

                foreach ($forms as $formName => $dates) {
                    $formArray[] = [
                        $formName => $dates
                    ];
                }

                $processedData[$implodeUnderscore][] = [
                    $riceType => $formArray
                ];
            }
        }

        return response()->json([
            'errors' => null,
            'livePriceStatusMessage' => $LivePriceStatusMessage,
            'prices' => $processedData,
            'closing' => [$ricetype => $livePricesClosingOpening],
            'latest' => Carbon::parse($lastEnteredRecord->created_at)->format('Y-m-d'),
            'lastUpdatedDate' => $this->livePricesGlobalLastUpdatedAtFormatted($cropYear),
        ]);
    }

    private function invalidLatestLivePriceTupleKeys($state, $cropYear = null): array
    {
        $latestIds = LivePrice::query()
            ->selectRaw('MAX(id) as id')
            ->where('name', '!=', '0')
            ->where('form', '!=', '0')
            ->where('state', $state)
            ->when($cropYear !== null && $cropYear !== '', fn ($q) => $q->where('cropYear', $cropYear))
            ->groupBy('name', 'form', 'state', 'cropYear');

        return LivePrice::query()
            ->whereIn('id', $latestIds)
            ->get(['name', 'form', 'state', 'cropYear', 'min_price', 'max_price'])
            ->filter(fn ($row) => ! $this->hasUsableLivePrice($row))
            ->mapWithKeys(fn ($row) => [$this->livePriceTupleKey($row) => true])
            ->all();
    }

    /**
     * Latest closing price per name+form tuple (one query instead of per-row subselect).
     *
     * @return array<string, mixed>
     */
    private function latestLivePriceClosingMap(string $state, $cropYear = null): array
    {
        $latestIdsQuery = DB::table('live_price_closing')
            ->selectRaw('MAX(id) as id')
            ->where('state', $state)
            ->when($cropYear !== null && $cropYear !== '', fn ($q) => $q->where('cropYear', $cropYear))
            ->groupBy('name', 'form', 'state', 'cropYear');

        $rows = DB::table('live_price_closing')
            ->whereIn('id', $latestIdsQuery)
            ->get(['name', 'form', 'state', 'cropYear', 'closing']);

        $map = [];
        foreach ($rows as $row) {
            $map[implode('|', [
                (string) $row->name,
                (string) $row->form,
                (string) $row->state,
                (string) $row->cropYear,
            ])] = $row->closing;
        }

        return $map;
    }

    private function livePriceTupleKey($row): string
    {
        return implode('|', [
            (string) ($row->name ?? ''),
            (string) ($row->form ?? ''),
            (string) ($row->state ?? ''),
            (string) ($row->cropYear ?? ''),
        ]);
    }

    private function hasUsableLivePrice($row): bool
    {
        return $this->isPositiveLivePriceValue($row->min_price ?? null)
            && $this->isPositiveLivePriceValue($row->max_price ?? null);
    }

    private function isPositiveLivePriceValue($value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return is_numeric($value) && (float) $value > 0;
    }


    public function getDesignation()
    {
        $designation = Designation::orderBy('orders')->get();
        return response()->json([
            'errors' => null,
            'data' => $designation
        ]);

    }

    public function getPorts()
    {
        $lastUpdatedDate = Port::orderBy('created_at', 'DESC')->first();

        $dateCreate = date_create($lastUpdatedDate->created_at);
        $formatedDate = date_format($dateCreate, 'Y/m/d');
        $listPort = Port::whereDate('created_at', $formatedDate)->orderBy('id', 'DESC')->where('route', '!=', 0)->get()->groupBy('state');

        return response()->json(['errors' => null, 'list' => $listPort]);
    }

    public function getpriceByTimePeriod($state, $riceType, $rice, $timePeriod, Request $request)
    {

        $state = $this->decodeEncodedRouteSegment((string) $state);
        $riceType = $this->decodeEncodedRouteSegment((string) $riceType);
        $rice = $this->decodeEncodedRouteSegment((string) $rice);
        $timePeriod = $this->decodeEncodedRouteSegment((string) $timePeriod);

        $rice = str_replace('_', ' ', $rice);

        $todayDate = Carbon::now();
        $created_at = [];
        $min_price = [];
        $max_price = [];
        $lowValue = 0;
        $highValue = 0;
        $combinedData = [];
        $lowDate = 0;
        $highDate = 0;
        $constantValue = 0;

        $riceName = $this->resolveRiceNameFromEncodedInput((string) $rice);

        if (! $riceName) {
            return response()->json([
                'errors' => ['rice' => 'Rice not found'],
                'message' => 'Invalid rice name or id for the given encoded value.',
            ], 404);
        }

        $productType = RiceName::select('type')->where('id', $riceName->id)->first();

        if (! $productType || $productType->type === null) {
            return response()->json([
                'errors' => ['rice' => 'Product type not found'],
            ], 404);
        }

        if( $request->has('year') ){
            $year = $request->year;
        }

        $type = $this->resolveRiceFormForProductType($riceType, (string) $productType->type);

        if (! $type) {
            return response()->json([
                'errors' => ['riceType' => 'Rice form not found for this product type'],
            ], 404);
        }

        $fromDate = $todayDate->format('y-m-d');

        $explodeTime = explode('_', $timePeriod);
        $lookbackDays = 30;
        if (preg_match('/^\d+$/', trim((string) $timePeriod))) {
            $lookbackDays = max(1, min(2000, (int) trim((string) $timePeriod)));
        } elseif (count($explodeTime) > 1 && is_numeric($explodeTime[0])) {
            $n = (int) $explodeTime[0];
            $unit = strtolower((string) ($explodeTime[1] ?? ''));
            if (str_starts_with($unit, 'day')) {
                $lookbackDays = max(1, $n);
            } elseif (str_starts_with($unit, 'month')) {
                $lookbackDays = max(1, min(2000, $n * 30));
            }
        }

        if (! $request->has('year')) {
            $lookbackDays = max($lookbackDays, 365);
        }

        $formRelConstraint = function ($query) use ($productType) {
            $query->where('type', $productType->type);
        };

        if ($request->has('year')) {
            // Crop-year charts: use all rows for that crop year up to today (do not restrict to last N calendar days).
            $periodEnd = $todayDate->copy()->format('Y-m-d');

            $prices = LivePrice::where('name', $riceName->id)->where('form', $type->id)->with([
                'name_rel',
                'form_rel' => $formRelConstraint,
            ])->where(['state' => $state])
                ->where(function ($q) use ($year) {
                    $this->applyLivePriceCropYearMatch($q, $year);
                })
                ->whereDate('created_at', '<=', $periodEnd)
                ->get();
        } else {
             if( count($explodeTime) > 1 ){
                $periodEnd = $todayDate->copy()->format('Y-m-d');
                $periodStart = $todayDate->copy()->subDays($lookbackDays)->format('Y-m-d');
                $prices = LivePrice::where('name', $riceName->id)->where('form', $type->id)->with([
                    'name_rel',
                    'form_rel' => $formRelConstraint,
                ])->where(['state' => $state])
                    ->whereDate('created_at', '>=', $periodStart)
                    ->whereDate('created_at', '<=', $periodEnd)
                    ->get();

            } else {
                $periodEnd = $todayDate->copy()->format('Y-m-d');
                $periodStart = $todayDate->copy()->subDays($lookbackDays)->format('Y-m-d');
                $prices = LivePrice::where('name', $riceName->id)->where('form', $type->id)->with([
                    'name_rel',
                    'form_rel' => $formRelConstraint,
                ])->where(['state' => $state])
                    ->whereDate('created_at', '>=', $periodStart)
                    ->whereDate('created_at', '<=', $periodEnd)
                    ->get();
            }
        }

        // Multiple updates on the same day: keep only the last added row per IST calendar day.
        $pricesLastEntryPerDay = $this->collapseLivePricesToLatestPerDay($prices);

        // Earliest / latest IST dates in the series (season open = first chart point for these params).
        $seasonOpeningDate = null;
        $latestIstDate = null;
        if ($pricesLastEntryPerDay->isNotEmpty()) {
            $seasonOpeningDate = $pricesLastEntryPerDay
                ->map(function ($r) {
                    return $r->created_at->copy()->timezone('Asia/Kolkata')->format('Y-m-d');
                })
                ->min();
            $latestIstDate = $pricesLastEntryPerDay
                ->map(function ($r) {
                    return $r->created_at->copy()->timezone('Asia/Kolkata')->format('Y-m-d');
                })
                ->max();
        }

        $cropYearForOpening = $request->has('year') ? (string) $request->year : null;
        if ($cropYearForOpening === null && $latestIstDate) {
            $rowForLatest = $pricesLastEntryPerDay->first(function ($r) use ($latestIstDate) {
                return $r->created_at->copy()->timezone('Asia/Kolkata')->format('Y-m-d') === $latestIstDate;
            });
            if ($rowForLatest && $rowForLatest->cropYear !== null && $rowForLatest->cropYear !== '') {
                $cropYearForOpening = (string) $rowForLatest->cropYear;
            }
        }

        $latestDateOpeningPrice = null;
        if ($latestIstDate && $cropYearForOpening) {
            $openingRecord = LivePricesOpeningClosing::where('name', $riceName->id)
                ->where('form', $type->id)
                ->where('state', $state)
                ->where(function ($q) use ($cropYearForOpening) {
                    $this->applyLivePriceCropYearMatch($q, $cropYearForOpening);
                })
                ->whereNotNull('opening')
                ->where('opening', '!=', '')
                ->orderByDesc('id')
                ->first();
            if ($openingRecord !== null && is_numeric($openingRecord->opening)) {
                $latestDateOpeningPrice = (float) $openingRecord->opening;
            }
        }

        foreach ($pricesLastEntryPerDay as $k => $v) {
            $created_at[] = strtotime($v->created_at->copy()->timezone('Asia/Kolkata')->format('y-m-d'));
            $max_price[] = $v->max_price;
        }

        $combine = array_combine($created_at, $max_price);
        $arrayValuesPrices = '';
        $maxCount = 0;
        if( $combine ){
            $lowValue = min($combine);
            $highValue = max($combine);

            $lowDate  = Carbon::parse(array_search($lowValue, $combine), 'UTC')
                    ->setTimezone('Asia/Kolkata')
                    ->format('d-m-Y');

            $highDate = Carbon::parse(array_search($highValue, $combine), 'UTC')
                    ->setTimezone('Asia/Kolkata')
                    ->format('d-m-Y');

            // Ensure chronological order by timestamp key before building series
            $sortedCombine = $combine;
            ksort($sortedCombine);
            $combinedData = [];
            foreach ($sortedCombine as $kk => $vv) {
                $combinedData[] = [$kk * 1000, (int)$vv];
            }

            $seq = array_values($sortedCombine);
            $arrayValuesPrices = $seq;
            $bestVal = null;
            $bestLen = 0;
            $currVal = null;
            $currLen = 0;
            foreach ($seq as $val) {
                $num = is_numeric($val) ? (float) $val : null;
                if ($num === null || $num <= 0) {
                    $currVal = null;
                    $currLen = 0;
                    continue;
                }
                if ($currVal === null || $num != $currVal) {
                    $currVal = $num;
                    $currLen = 1;
                } else {
                    $currLen++;
                }
                if ($currLen > $bestLen) {
                    $bestLen = $currLen;
                    $bestVal = $num;
                }
            }
            $constantValue = $bestVal !== null ? $bestVal : 0;
            $maxCount = $bestLen;
            
        }
        $responseData = [
            'errors' => null,
            'date' => $created_at,
            'prices' => $arrayValuesPrices,
            'combinedData' => $combinedData,
            'productType' => $productType,
            'lowValue' => $lowValue,
            'lowDate' => $lowDate,
            'highDate' => $highDate,
            'highValue' => $highValue,
            'constantValue' => $constantValue,
            'maxCountConstant' => $maxCount,
            'seasonOpeningDate' => $seasonOpeningDate,
            'latestDate' => $latestIstDate,
            'latestDateOpeningPrice' => $latestDateOpeningPrice,
        ];

        return response()->json($responseData);

        if ($timePeriod == '15_Days') {
            $fromDate = $todayDate->subDays(15)->format('y-m-d');
        }

        if ($timePeriod == '1_Month') {
            $fromDate = $todayDate->subDays(30)->format('y-m-d');
        }

        if ($timePeriod == '2_Month') {
            $fromDate = $todayDate->subDays(60)->format('y-m-d');
        }

        if ($timePeriod == '3_Month') {
            $fromDate = $todayDate->subDays(90)->format('y-m-d');
        }

        if ($timePeriod == '4_Month') {
            $fromDate = $todayDate->subDays(120)->format('y-m-d');
        }

        if ($timePeriod == '5_Month') {
            $fromDate = $todayDate->subDays(150)->format('y-m-d');
        }

        if ($timePeriod == '6_Month') {
            $fromDate = $todayDate->subDays(180)->format('y-m-d');
        }

        if ($timePeriod == '7_Month') {
            $fromDate = $todayDate->subDays(210)->format('y-m-d');
        }

        if ($timePeriod == '8_Month') {
            $fromDate = $todayDate->subDays(240)->format('y-m-d');
        }

        if ($timePeriod == '9_Month') {
            $fromDate = $todayDate->subDays(270)->format('y-m-d');
        }

        if ($timePeriod == '10_Month') {
            $fromDate = $todayDate->subDays(300)->format('y-m-d');
        }

        if ($timePeriod == '11_Month') {
            $fromDate = $todayDate->subDays(330)->format('y-m-d');
        }

        if ($timePeriod == '12_Month') {
            $fromDate = $todayDate->subDays(360)->format('y-m-d');
        }

        $prices = LivePrice::where('name', $riceName->id)->where('form', $type->id)->with([
            'name_rel',
            'form_rel' => function ($query) use ($riceType) {
                return $query->where('type', $riceType)->get();
            }
        ])->where(['state' => $state])->where(DB::raw('date(created_at)'), '>', $fromDate)->get();

        foreach ($prices as $k => $v) {
            $created_at[] = $v->created_at->format('y-m-d');
        }
        foreach ($prices as $key => $value) {
            $max_price[] = $value->max_price;
        }
        $responseData = ['errors' => null, 'date' => $created_at, 'prices' => $max_price];
        return response()->json($responseData);
    }

    /**
     * Chart records endpoint with fixed state/time period:
     * - route: /api/get/price/chart/records/{encodedRiceType}/{encodedRice}
     * - state: PUNJAB-HARYANA (hardcoded; matches getPrices / live_prices.state)
     * - period: 365 days minimum (hardcoded)
     */
    public function getPriceChartRecords($encodedRiceType, $encodedRice)
    {
        // Match live_prices.state values used by getPrices() / admin (typically PUNJAB-HARYANA).
        $state = 'PUNJAB-HARYANA';
        $riceType = $this->decodeEncodedRouteSegment((string) $encodedRiceType);
        $riceInput = $this->decodeEncodedRouteSegment((string) $encodedRice);
        $lookbackDays = 365;

        $todayDate = Carbon::now();
        $created_at = [];
        $max_price = [];
        $lowValue = 0;
        $highValue = 0;
        $combinedData = [];
        $lowDate = 0;
        $highDate = 0;
        $constantValue = 0;

        $riceName = $this->resolveRiceNameFromEncodedInput($riceInput);

        if (! $riceName) {
            return response()->json([
                'errors' => ['rice' => 'Rice not found'],
                'message' => 'Invalid rice name or id for the given encoded value.',
            ], 404);
        }

        $productType = RiceName::select('type')->where('id', $riceName->id)->first();

        if (! $productType || $productType->type === null) {
            return response()->json(['errors' => ['rice' => 'Product type not found']], 404);
        }

        $riceForm = $this->resolveRiceFormForProductType($riceType, (string) $productType->type);

        if (! $riceForm) {
            return response()->json(['errors' => ['riceType' => 'Rice form not found']], 404);
        }

        $productTypeValue = (string) $productType->type;
        $periodEnd = $todayDate->copy()->format('Y-m-d');
        $periodStart = $todayDate->copy()->subDays($lookbackDays)->format('Y-m-d');
        $prices = LivePrice::where('name', $riceName->id)
            ->where('form', $riceForm->id)
            ->with([
                'name_rel',
                'form_rel' => function ($query) use ($productTypeValue) {
                    return $query->where('type', $productTypeValue)->get();
                }
            ])
            ->where(['state' => $state])
            ->whereDate('created_at', '>=', $periodStart)
            ->whereDate('created_at', '<=', $periodEnd)
            ->get();

        $pricesLastEntryPerDay = $this->collapseLivePricesToLatestPerDay($prices);

        foreach ($pricesLastEntryPerDay as $v) {
            $created_at[] = strtotime($v->created_at->copy()->timezone('Asia/Kolkata')->format('y-m-d'));
            $max_price[] = $v->max_price;
        }

        $combine = array_combine($created_at, $max_price);
        $arrayValuesPrices = '';
        $maxCount = 0;
        if ($combine) {
            $lowValue = min($combine);
            $highValue = max($combine);

            $lowDate = Carbon::parse(array_search($lowValue, $combine), 'UTC')
                ->setTimezone('Asia/Kolkata')
                ->format('d-m-Y');

            $highDate = Carbon::parse(array_search($highValue, $combine), 'UTC')
                ->setTimezone('Asia/Kolkata')
                ->format('d-m-Y');

            $sortedCombine = $combine;
            ksort($sortedCombine);
            foreach ($sortedCombine as $kk => $vv) {
                $combinedData[] = [$kk * 1000, (int) $vv];
            }
            $seq = array_values($sortedCombine);
            $arrayValuesPrices = $seq;
            $bestVal = null;
            $bestLen = 0;
            $currVal = null;
            $currLen = 0;
            foreach ($seq as $val) {
                $num = is_numeric($val) ? (float) $val : null;
                if ($num === null || $num <= 0) {
                    $currVal = null;
                    $currLen = 0;
                    continue;
                }
                if ($currVal === null || $num != $currVal) {
                    $currVal = $num;
                    $currLen = 1;
                } else {
                    $currLen++;
                }
                if ($currLen > $bestLen) {
                    $bestLen = $currLen;
                    $bestVal = $num;
                }
            }
            $constantValue = $bestVal !== null ? $bestVal : 0;
            $maxCount = $bestLen;
        }

        return response()->json([
            'errors' => null,
            'date' => $created_at,
            'prices' => $arrayValuesPrices,
            'combinedData' => $combinedData,
            'productType' => $productType,
            'lowValue' => $lowValue,
            'lowDate' => $lowDate,
            'highDate' => $highDate,
            'highValue' => $highValue,
            'constantValue' => $constantValue,
            'maxCountConstant' => $maxCount,
        ]);
    }

    public function getGalleryData(Request $request)
    {
        $limit = (int) $request->query('limit', 0);

        if ($limit > 0) {
            // Last N per type (basmati + nonbasmati), then group by type.
            $types = ['basmati', 'nonbasmati'];
            $rows = collect();

            foreach ($types as $type) {
                $rows = $rows->merge(
                    Gallery::where('type', $type)
                        ->orderBy('id', 'desc')
                        ->limit($limit)
                        ->get()
                );
            }

            $gallery = $rows->groupBy('type');
        } else {
            $gallery = Gallery::get()->groupBy('type');
        }

        return response()->json(['errors' => null, 'data' => $gallery]);
    }

    public function getGalleryDetails($galleryId)
    {
        $gallery = Gallery::whereId($galleryId)->first();

        $specif = json_decode($gallery->spec, true);
        $moreSpec = [];
        foreach ($specif as $k => $v) {
            $moreSpec[][$k] = $v;
        }
        $gallery['specification'] = $moreSpec;
        return response()->json(['error' => null, 'data' => $gallery]);
    }

    public function saveUser(Request $request)
    {
        $trialPeriod = TrialPeriod::first();
        $newExpiryDate = FreeTrialMonths::first();

        $expiredDate = null;
        if ($trialPeriod) {
            $trialPeriodMonth = $trialPeriod->month;
            // $month = $newExpiryDate->month;
            // $expiredDate = Carbon::now()->addMonth($month)->format('Y-m-d');
            $expiredDate = Carbon::now()->addDays(30)->format('Y-m-d');
        }

        $data = [
            'buyer' => 5,
            'supplier' => 6,
            'broker' => 7,
            'guest' => 8
        ];

        $hasEmail = User::where(['email' => $request->email, 'status' => 1,'userType' => 1])->get();
        if ($hasEmail->count() > 0) {
            return response()->json(['error' => 'Email already exist.', 'data' => []], 500);
        }

        $hasMobile = User::where(['mobile' => $request->mobile, 'status' => 1 , 'userType' => 1])->get();
        if ($hasMobile->count() > 0) {
            return response()->json(['error' => 'Mobile Number already exist.', 'data' => []], 500);
        }

        $otp = rand(1111, 9999);
        // if( $request->has('registerForm') ){
        //     $user = User::create([
        //             'name' => $request->username,
        //             'email' => $request->email,
        //             'password' => Hash::make($request->password),
        //             'mobile' => $request->mobile,
        //             'address' => $request->address,
        //             'contact_person_name' => $request->contactperson,
        //             'companyname' => $request->companyname,
        //             'role' => 0,
        //             'otp' => $otp,
        //             'bagCategory' => $request->bagCategory,
        //             'expired_on' => $expiredDate,
        //             'status' => 0,
        //             'usd_role' => 6,
        //             'is_INR_active' => 0,
        //             'is_usd_active' => 1
        //         ]);
        // }else{
        User::where(['email' => $request->email , 'status' => 0])->delete();
        if ($request->has('zipcode')) {
            $user = User::create([
                'name' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mobile' => $request->mobile,
                'country' => $request->country,
                'zip_code' => $request->zipcode,
                'import_port' => $request->import_port,
                'address' => $request->address,
                'contact_person_name' => $request->contactperson,
                'companyname' => $request->companyname,
                'role' => 0,
                'otp' => $otp,
                'bagCategory' => ($request->userState != 8) ? $request->bagCategory : 0,
                'expired_on' => Carbon::now()->addDays(365)->format('Y-m-d'),
                'status' => 0,
                'userType' => 1,
                'usd_role' => $data[$request->userState],
                'is_INR_active' => 0,
                'is_usd_active' => 1
            ]);
        } else {
            $user = User::create([
                'name' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mobile' => $request->mobile,
                'companyname' => $request->companyname,
                'role' => $data[$request->userState],
                'otp' => $otp,
                'expired_on' => Carbon::now()->addMonth(536)->format('Y-m-d'),
                'status' => 0,
                'userType' => 1,
                // 'usd_role' => 0,
                'usd_role' => 6,
                'is_INR_active' => 1,
                'is_usd_active' => 1,


            ]);
        }
        // }


        User::where('mobile', $request->mobile)->update(['otp' => $otp]);
        file_get_contents('http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto=' . $request->mobile . '&message=Thank+you+for+registering+on+SNTC+Rice+Live+Pricing+App.+Your+OTP+Code+is+' . $otp . '.+SNTCAL&PEID=1701172916686910712&templateid=1707172924575773908');

        if ($user) {
            if ($user->email != null) {
                $response = MailController::generateMailForOTPThanks($user->email, 'no@replay.in', 'SNTC GROUP', 'Thank you for registering on SNTC Rice Live Pricing App.', 'Thank you for registering on SNTC Rice Live Pricing App.', $otp);
            }
            return response()->json(['error' => null, 'data' => User::where('id', $user->id)->first()], 200);
        } else {
            return response()->json(['error' => "Something went wrong.", 'data' => []], 500);
        }
    }

    /**
     * Mobile app: check whether an email is already registered.
     * Registration (no user_id): matches saveUser — active users only (status=1, userType=1).
     * Profile update (with user_id): matches updateUser — any other user with same email.
     */
    public function checkEmailExists(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'user_id' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'exists' => false,
                'message' => 'Invalid email.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = trim((string) $request->email);
        $query = User::query()
            ->where('email', $email)
            ->where('userType', 1);

        if ($request->filled('user_id')) {
            $query->where('id', '!=', (int) $request->user_id);
        } else {
            $query->where('status', 1);
        }

        $exists = $query->exists();

        return response()->json([
            'status' => true,
            'exists' => $exists,
            'message' => $exists ? 'Email already exists.' : 'Email is available.',
        ], 200);
    }

    public function updateUser(Request $request)
    {
        $data = [
            'buyer' => 5,
            'supplier' => 6,
            'broker' => 7,
            'guest' => 8,
            'Buyer' => 5,
            'Supplier' => 6,
            'Broker' => 7,
            'Guest' => 8,
        ];
        $hasEmail = User::where(['email' => $request->email ,'userType' => 1])->where('id', '!=', $request->userId)->get();
        if ($hasEmail->count() > 0) {
            return response()->json(['error' => 'Email already exist.', 'data' => []], 500);
        }

        $hasMobile = User::where(['mobile' => $request->mobile , 'userType' => 1])->where('id', '!=', $request->userId)->get();
        if ($hasMobile->count() > 0) {
            return response()->json(['error' => 'Mobile Number already exist.', 'data' => []], 500);
        }

        $otp = rand(1111, 9999);
        $user = User::where('id', $request->userId)->where('userType' , 1)->update([
            'name' => $request->username,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'companyname' => $request->companyname,
            // 'role' => $data[$request->userState],
        ]);

        $user = User::where('userType' , 1)->where('id', $request->userId)->first();
        if ($user) {
            return response()->json(['error' => null, 'data' => $user], 200);
        } else {
            return response()->json(['error' => "Something went wrong.", 'data' => []], 500);
        }
    }

    public function verifyUser(Request $request)
    {
        $userDetails = User::where(['mobile' => $request->mobile, 'otp' => $request->otp,'userType' => 1])->first();

        if ($userDetails != null) {
            User::where(['mobile' => $request->mobile, 'otp' => $request->otp,'userType' => 1])->update(['status' => 1]);
            $userDetails = User::where('id', $userDetails->id)->with(['role_rel', 'role_rel_usd'])->first();
            $token = trim((string) ($userDetails->mobile_api_token ?? $userDetails->api_token ?? ''));
            if ($token === '') {
                $token = $this->rotateAppSessionForUser($userDetails);
                $userDetails = User::where('id', $userDetails->id)->with(['role_rel', 'role_rel_usd'])->first();
            }

            return $this->appLoginSuccessResponse($userDetails, $token);
        } else {
            return response()->json(['error' => "Wrong OTP.", 'data' => []], 500);
        }
    }

    public function verifyOTP($number, $otp)
    {
        $user = User::where(['mobile' => $number, 'otp' => $otp, 'userType' => 1])->first();
        if ($user) {
            $user = User::where('id', $user->id)->with(['role_rel', 'role_rel_usd'])->first();
            $token = trim((string) ($user->mobile_api_token ?? $user->api_token ?? ''));
            if ($token === '') {
                $token = $this->rotateAppSessionForUser($user);
                $user = User::where('id', $user->id)->with(['role_rel', 'role_rel_usd'])->first();
            }

            // Keep legacy `data` (OTP) and add full login session fields for the verifying device.
            $payload = $this->appLoginSuccessResponse($user, $token)->getData(true);
            $payload['data'] = $otp;
            $payload['error'] = null;

            return response()->json($payload, 200);
        }

        return response()->json(['error' => null, 'data' => null], 500);
    }

    public function changePassword(Request $request)
    {
        $user = User::where(['mobile' => $request->number,'userType' => 1])->update(['password' => Hash::make($request->password)]);
        if ($user != '') {
            return response()->json(['error' => null, 'data' => null]);
        } else {
            return response()->json(['error' => null, 'data' => null], 500);
        }
    }

    public function getBasmatiState(Request $request)
    {
        $ricetype = 'basmati';

        $ricename = RiceName::where('type', 'basmati')->pluck('id')->toArray();

        $lastRecord = LivePrice::where('name', '!=', 0)
            ->where('form', '!=', 0)
            ->whereNotNull('min_price')
            ->whereNotNull('max_price')
            ->orderByDesc('id')
            ->first();

        if (!$lastRecord) {
            return response()->json(['error' => 'No records found', 'data' => []], 404);
        }

        $lastEnteredRecord = $lastRecord->created_at->format('Y-m-d');


        $livePrice = LivePrice::whereNotNull('state_order')
            ->whereNotNull('min_price')
            ->whereNotNull('max_price')
            ->whereIn('name', $ricename)
            ->orderBy('state_order', 'ASC');

        if ($request->has('year')) {

            $today = Carbon::now();
            $todayYear = $today->year;
            $cropYear = $request->year;

            $date = Carbon::parse($lastEnteredRecord)->format('d');
            $month = Carbon::parse($lastEnteredRecord)->format('m');


            $lastEnteredRecord = Carbon::createFromDate($cropYear, $month, $date)->format('Y-m-d');

            //closing Data
            $livePrice = LivePrice::where(function ($q) {
                                $q->whereNull('closing')->orWhere('closing', '');
                            })
                ->whereNotNull('min_price')
                ->whereNotNull('max_price')
                // ->where('cropYear' , $cropYear)
                ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
                ->with([
                    'name_rel',
                    'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', 'ASC')
                ])
                // ->orderBy('id' , 'desc')
                ->orderBy('state_order' , 'ASC')->whereDate('created_at' , $lastEnteredRecord);

            if (!$livePrice->exists()) {

                $lastToLastDateData = LivePrice::where('name', '!=', '0')->where('form', '!=', '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('created_at', 'DESC')->whereDate('created_at', '<', $lastEnteredRecord)->first();

                $livePrice = LivePrice::whereNotNull('min_price')
                    ->whereNotNull('max_price')->orderBy('state_order' , 'ASC')
                    ->whereDate('created_at' , $lastToLastDateData->created_at->format('Y-m-d'));
            }
        } else {
            $livePrice = $livePrice->whereDate('created_at', $lastEnteredRecord);
        }

        $states = $livePrice->distinct()->pluck('state');

        return response()->json(['error' => null, 'data' => $states], 200);
    }
    public function getBasmatiStateForWeb(Request $request)
    {
        return response()->json([
            'error' => null,
            'data' => $this->resolveWebRiceTypeStates('basmati', $request->get('year')),
        ], 200);
    }

    /**
     * Ordered unique states for web live-price state pickers (basmati / non-basmati).
     *
     * @return array<int, string>
     */
    private function resolveWebRiceTypeStates(string $ricetype, $yearParam = null): array
    {
        $cacheKey = 'web_rice_states:' . $ricetype . ':' . (string) ($yearParam ?? 'latest');

        return Cache::remember($cacheKey, 60, function () use ($ricetype, $yearParam) {
            return $this->computeWebRiceTypeStates($ricetype, $yearParam);
        });
    }

    /**
     * @return array<int, string>
     */
    private function computeWebRiceTypeStates(string $ricetype, $yearParam = null): array
    {
        $latestCropYear = LivePrice::query()->max('cropYear');
        if ($latestCropYear === null) {
            return [];
        }

        $cropYear = ($yearParam !== null && $yearParam !== '') ? $yearParam : $latestCropYear;
        $asOfDate = Carbon::now()->toDateString();

        $lastPriceAt = LivePrice::query()
            ->where('name', '!=', '0')
            ->where('form', '!=', '0')
            ->whereNotNull('min_price')
            ->whereNotNull('max_price')
            ->where('cropYear', $cropYear)
            ->whereDate('created_at', '<=', $asOfDate)
            ->orderByDesc('created_at')
            ->value('created_at');

        if (! $lastPriceAt) {
            return [];
        }

        $lastDate = Carbon::parse($lastPriceAt)->toDateString();

        $closingRows = DB::table('live_price_closing as lpc')
            ->join('rice_forms as rf', function ($join) use ($ricetype) {
                $join->on('rf.id', '=', 'lpc.form')
                    ->where('rf.type', '=', $ricetype)
                    ->where('rf.status', '=', 1);
            })
            ->join('rice_names as rn', function ($join) use ($ricetype) {
                $join->on('rn.id', '=', 'lpc.name')
                    ->where('rn.type', '=', $ricetype);
            })
            ->where('lpc.cropYear', $cropYear)
            ->whereNotNull('lpc.closing')
            ->where('lpc.closing', '!=', '')
            ->select('lpc.name', 'lpc.form', 'lpc.state')
            ->get();

        $closedNameForms = [];
        $closingStates = [];
        foreach ($closingRows as $row) {
            $closedNameForms[strtolower($row->name . '_' . $row->form)] = true;
            if ($row->state !== null && $row->state !== '') {
                $closingStates[$row->state] = true;
            }
        }

        $liveQuery = DB::table('live_prices as lp')
            ->join('rice_forms as rf', function ($join) use ($ricetype) {
                $join->on('rf.id', '=', 'lp.form')
                    ->where('rf.type', '=', $ricetype)
                    ->where('rf.status', '=', 1);
            })
            ->join('rice_names as rn', 'rn.id', '=', 'lp.name')
            ->where('lp.name', '!=', '0')
            ->where('lp.form', '!=', '0')
            ->whereNotNull('lp.min_price')
            ->whereNotNull('lp.max_price')
            ->where('lp.cropYear', $cropYear)
            ->whereDate('lp.created_at', $lastDate)
            ->select('lp.state', 'lp.name', 'lp.form', 'lp.state_order');

        if ((string) $cropYear === '2023' || (int) $cropYear === 2023) {
            $liveQuery->whereRaw('LOWER(rf.form_name) NOT LIKE ?', ['%new crop%']);
        }

        $stateOrders = [];
        foreach ($liveQuery->get() as $row) {
            if ($closedNameForms !== [] && isset($closedNameForms[strtolower($row->name . '_' . $row->form)])) {
                continue;
            }
            if ($row->state === null || $row->state === '') {
                continue;
            }
            $order = $row->state_order !== null ? (int) $row->state_order : PHP_INT_MAX;
            if (! isset($stateOrders[$row->state]) || $order < $stateOrders[$row->state]) {
                $stateOrders[$row->state] = $order;
            }
        }

        foreach (array_keys($closingStates) as $state) {
            if (! isset($stateOrders[$state])) {
                $stateOrders[$state] = PHP_INT_MAX;
            }
        }

        $needOrder = [];
        foreach ($stateOrders as $state => $order) {
            if ($order === PHP_INT_MAX) {
                $needOrder[] = $state;
            }
        }
        if ($needOrder !== []) {
            $orderMap = DB::table('live_prices')
                ->whereIn('state', $needOrder)
                ->whereNotNull('state_order')
                ->groupBy('state')
                ->select('state', DB::raw('MIN(state_order) as state_order'))
                ->pluck('state_order', 'state');
            foreach ($needOrder as $state) {
                if (isset($orderMap[$state])) {
                    $stateOrders[$state] = (int) $orderMap[$state];
                }
            }
        }

        // Match prior behavior: only keep states that have a known state_order.
        $stateOrders = array_filter(
            $stateOrders,
            static fn ($order) => $order !== PHP_INT_MAX
        );
        asort($stateOrders, SORT_NUMERIC);

        return array_values(array_keys($stateOrders));
    }

    


    public function getNONBasmatiState(Request $request)
    {
        $ricename = RiceName::select('id')->where('type', 'non-basmati')->pluck('id')->toArray();
        $ricetype = 'non-basmati';


        $ricename = RiceName::where('type', 'basmati')->pluck('id')->toArray();

        $lastRecord = LivePrice::where('name', '!=', 0)
            ->where('form', '!=', 0)
            ->whereNotNull('min_price')
            ->whereNotNull('max_price')
            ->orderByDesc('id')
            ->first();

        if (!$lastRecord) {
            return response()->json(['error' => 'No records found', 'data' => []], 404);
        }

        $lastEnteredRecord = $lastRecord->created_at->format('Y-m-d');
        $today = Carbon::now();
        $todayYear = $today->year;
        $cropYear = $request->year;

        $year = $today->year;

        $date = Carbon::parse($lastEnteredRecord)->format('d');
        $month = Carbon::parse($lastEnteredRecord)->format('m');

        $lastEnteredRecord = Carbon::createFromDate($cropYear, $month, $date)->format('Y-m-d');

        $livePrice = LivePrice::whereNotNull('state_order')
            ->whereNotNull('min_price')
            ->whereNotNull('max_price')
            ->whereIn('name', $ricename)
            ->orderBy('state_order', 'ASC');

        if ($year != '') {
            // $livePricesClosingOpening = LivePricesOpeningClosing::select(["id","trade_for","farming_type","name","form","cropYear","state","opening","closing"])
            //     // ->where('state', $state)
            //     ->where('cropYear', $cropYear)
            //     ->where(function ($q) {
            //         $q->whereNotNull('closing')->where('closing', '!=', '');
            //     })
            //     ->whereHas('name_rel', fn($q) => $q->where('type', $ricetype))
            //     // ->whereHas('form_rel')
            //     ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
            //     ->with([
            //         'name_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC"),
            //         // 'form_rel'
            //         'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
            //     ])
            //     ->get();

            //     $hasClosingName = $livePricesClosingOpening->pluck('name');
            //     $hasClosingForm = $livePricesClosingOpening->pluck('form');

            //     $hasOpenigClosingConcade = [];
            //     foreach ($hasClosingName as $index => $key) {
            //         $hasOpenigClosingConcade[] = strtolower($key . '_' . $hasClosingForm[$index]);
            //     }


            //     $livePrice = LivePrice::query()
            //         ->has('name_rel')
            //         ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
            //         ->with([
            //             'name_rel',
            //             'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
            //         ])
            //         ->whereNotNull('min_price')
            //         ->whereNotNull('max_price')
            //         // ->where('state', $state)
            //         ->where('cropYear' , $cropYear)
            //         ->where(function ($q) {
            //                 $q->whereNull('closing')->orWhere('closing', '');
            //             })
            //         ->whereDate('created_at',$lastEnteredRecord)->get();

            //     if($livePrice->count() == 0){
            //         $livePrice = LivePrice::query()
            //             ->has('name_rel')
            //             ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
            //             ->with([
            //                 'name_rel',
            //                 'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
            //             ])
            //             ->where('cropYear' , $cropYear)
            //             ->whereNotNull('min_price')
            //             ->whereNotNull('max_price')
            //             // ->where('state', $state)
            //             ->where(function ($q) {
            //                 $q->whereNull('closing')->orWhere('closing', '');
            //             })
            //             ->whereDate('created_at','<',$lastEnteredRecord)->first();
            //     }

            //     if($livePrice){
            //         $processedData = [];
            //         foreach ($livePrice->sortBy('name_rel.order') as $v) {
            //             if ( count($hasOpenigClosingConcade) > 0 ){
            //                 $combineNameForm = $v->name.'_'.$v->form;
            //                 if( !in_array($combineNameForm, $hasOpenigClosingConcade) ) {
            //                     $processedData[] = $v->state;
            //                 }
            //             }else{
            //                 $processedData[] = $v->state;
            //             }
            //         }

            //         $fiilteredProcessedData = [];
            //         foreach ($livePrice->sortBy('form_rel.order') as $v) {

            //             if ( count($hasOpenigClosingConcade) > 0 ){
            //                 $combineNameForm = $v->name.'_'.$v->form;

            //                 if( !in_array($combineNameForm, $hasOpenigClosingConcade) ) {
            //                     $fiilteredProcessedData[] = $v->state;
            //                 }
            //             }else{
            //                 $fiilteredProcessedData[] = $v->state;
            //             }
                        
            //         }
            //     }
            //     $data = (array_values(array_unique($fiilteredProcessedData)));


            $today = Carbon::now();
            $todayYear = $today->year;
            $cropYear = $request->year;

            $date = Carbon::parse($lastEnteredRecord)->format('d');
            $month = Carbon::parse($lastEnteredRecord)->format('m');


            $lastEnteredRecord = Carbon::createFromDate($cropYear, $month, $date)->format('Y-m-d');

            //closing Data
            $livePrice = LivePrice::where(function ($q) {
                                $q->whereNull('closing')->orWhere('closing', '');
                            })
                ->whereNotNull('min_price')
                ->whereNotNull('max_price')
                ->where('cropYear' , $cropYear)
                ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
                ->with([
                    'name_rel',
                    'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', 'ASC')
                ])
                // ->orderBy('id' , 'desc')
                ->orderBy('state_order' , 'ASC')->whereDate('created_at' , $lastEnteredRecord);


            if (!$livePrice->exists()) {
                $lastToLastDateData = LivePrice::where('name', '!=', '0')->where('form', '!=', '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('created_at', 'DESC')->whereDate('created_at', '<', $lastEnteredRecord)->first();

                $livePrice = LivePrice::whereNotNull('min_price')->whereNotNull('max_price')->orderBy('state_order' , 'ASC')->whereDate('created_at' , $lastToLastDateData->created_at->format('Y-m-d'));
            }
        } else {
            $livePrice = $livePrice->whereDate('created_at', $lastEnteredRecord);
        }

            $states = $livePrice->distinct()->pluck('state')->values()->all();
            $sortArray = LivePrice::distinct('state')->orderBy('state_order')->pluck('state', 'state_order')->toArray();
            $orderByState = array_flip($sortArray); // state => order
            usort($states, function ($a, $b) use ($orderByState) {
                $oa = isset($orderByState[$a]) ? (int)$orderByState[$a] : PHP_INT_MAX;
                $ob = isset($orderByState[$b]) ? (int)$orderByState[$b] : PHP_INT_MAX;
                return $oa <=> $ob;
            });
            $sortedMap = [];
            foreach ($states as $s) {
                if (isset($orderByState[$s])) {
                    $sortedMap[(string) $orderByState[$s]] = $s;
                }
            }
            return response()->json(['error' => null, 'data' => $states, 'sorted' => [$sortedMap]], 200);
    }

    public function getNONBasmatiStateForWeb(Request $request)
    {
        return response()->json([
            'error' => null,
            'data' => $this->resolveWebRiceTypeStates('non-basmati', $request->get('year')),
        ], 200);
    }


    // public function getNONBasmatiState()
    // {
    //     $ricename = RiceName::select('id')->where('type', 'non-basmati')->pluck('id')->toArray();
    //     $lastRecord = LivePrice::where('name' ,'!=',0)->where('form' , '!=' , 0)->where('min_price', '!=', null)->where('max_price', '!=', null)->get()->last();

    //     if( $lastRecord != null ){
    //         $lastEnteredRecord = $lastRecord->created_at->format('Y-m-d');

    //         $livePrice = LivePrice::select('state')->whereDate('created_at',$lastEnteredRecord)->groupBy('state')->where('min_price', '!=', null)->where('max_price', '!=',null)
    //         ->whereIn('name', $ricename)->orderBy('order' , 'ASC')->pluck('state');

    //         // if( $livePrice->count() == 0 ){
    //         //     $lastRecord = LivePrice::whereDate('created_at' , '<' , $lastEnteredRecord )->get()->last();
    //         //     $lastEnteredRecord = $lastRecord->created_at->format('Y-m-d');

    //         //     $livePrice = LivePrice::whereDate('created_at',$lastEnteredRecord)->where('min_price', '!=', null)->where('max_price', '!=', null)
    //         //     ->whereIn('name', $ricename)->get()->map(function($query){
    //         //         return $query->state;
    //         //     });
    //         // }
    //         return response()->json(['error' => null, 'data' => $livePrice], 200);    
    //     }
    //     return response()->json(['error' => null, 'data' => ''], 200);
    // }

    public function getChartinterval()
    {
        $chartinterval = ChartInterval::select('id', 'name')->get();
        return response()->json(['chartinterval' => $chartinterval], 200);
    }

    public function isUserOrderExistAndActive($userId = null)
    {
        if ($userId) {
            $userOrder = Order::where('user_id', '=', $userId)->first();
            if ($userOrder) {
                $isActive = Carbon::now()->format('Y-m-d') <= $userOrder->end_date;
                return response()->json(['status' => 'success', 'isAccountActive' => $isActive], 200);
            }
            return response()->json(['status' => 'error', 'message' => 'Order Not Found'], 500);
        }
    }

    public function updateUserToken(Request $request)
    {
        // Legacy alias — same behavior as updateUserTokenById (supports portal + app users).
        return $this->updateUserTokenById($request);
    }

    // Update User Token

    // public function saveOrder(Request $request)
    // {
    //     $today = Carbon::now();
    //     $planModel = Plan::find($request->plan_id);
    //     $subPlanModel = SubPlan::find($request->sub_plan_id);
    //     $startDate = $today->format('Y-m-d');
    //     $subPlans = json_decode($planModel->sub_plan, true);
    //     $subPlanPrice = $subPlans[$request->sub_plan_id]['offerPrice'];

    //     if ($subPlanModel->name === "1 Year") {
    //         $endDate = $today->addYear(1)->format('Y-m-d');
    //     } else {
    //         if ($subPlanModel->name === "6 Month") {

    //             $endDate = $today->addMonth(6)->format('Y-m-d');
    //         } else {
    //             $endDate = $today->addMonth(1)->format('Y-m-d');
    //         }
    //     }

    //     $orderModel = new Order;
    //     $orderModel->user_id = $request->user_id;
    //     $orderModel->transaction_id = $request->transaction_id;
    //     $orderModel->plan_id = $request->plan_id;
    //     $orderModel->sub_plan_id = $request->sub_plan_id;
    //     $orderModel->plan_name = $planModel->plan_name;
    //     $orderModel->start_date = $startDate;
    //     $orderModel->end_date = $endDate;
    //     $orderModel->sub_plan_name = $subPlanModel->name;
    //     $orderModel->sub_plan_price = $subPlanPrice;
    //     $orderModel->status = 1;

    //     if ($orderModel->save()) {
    //         User::where(['id' => $request->user_id])->update(['expired_on' => $endDate]);
    //         return response()->json(['status' => 'success', 'last_inserted_id' => $orderModel->id], 200);
    //     }
    //     return response()->json(['status' => 'error'], 500);
    // }

    public function saveOrder(Request $request)
    {
        $today = Carbon::now();
        $planModel = USDPlan::find((int)$request->plan_id);
        $startDate = $today->format('Y-m-d');
        $endDate = $today->addMonth($planModel['valid_months'])->format('Y-m-d');
        //  

        $orderModel = new Order;
        $orderModel->user_id = $request->user_id;
        $orderModel->transaction_id = $request->transaction_id;
        $orderModel->plan_id = $request->plan_id;
        $orderModel->plan_name = $planModel->plan_name;
        $orderModel->start_date = $startDate;
        $orderModel->end_date = $endDate;
        $orderModel->payment_type = 'INR';
        $orderModel->amount = $planModel->discounted_prie;
        $orderModel->sub_plan_id = 0;
        $orderModel->sub_plan_name = '0';
        $orderModel->sub_plan_price = 0;
        $orderModel->status = 1;

        if ($orderModel->save()) {
            $userDetails = User::where(['id' => $request->user_id,'userType' => 1])->get();
            if ($userDetails->count() > 0) {
                $userUsdRole = $userDetails[0]['usd_role'];
                if ($userUsdRole != 0) {
                    User::where([
                        'id' => $request->user_id,
                        'userType' => 1
                    ])->update([
                        'expired_on' => $endDate, 
                        'is_usd_active' => 1, 
                        'transaction_id' => $request->transaction_id, 
                        'planId' => $request->plan_id
                    ]);
                    
                } else {
                    User::where(['id' => $request->user_id,'userType' => 1])->update(['expired_on' => $endDate, 'import_port' => 'Jebel Ali', 'usd_role' => 6, 'is_usd_active' => 1, 'transaction_id' => $request->transaction_id, 'planId' => $request->plan_id]);
                }
            }

            $userDetailsAfterPlanUpdate = User::where(['id' => $request->user_id,'userType' => 1])->get();

            return response()->json(['status' => 'success', 'last_inserted_id' => $orderModel->id, 'userDetails' => $userDetailsAfterPlanUpdate], 200);
        }
        return response()->json(['status' => 'error'], 500);
    }

    public function updateUserTokenById(Request $request)
    {
        $id = $request->input('id');
        $fcmToken = $request->input('user_token', $request->input('userToken'));

        if ($id === null || $id === '' || $id === 'undefined') {
            return response()->json(['status' => false, 'message' => 'User id is required'], 422);
        }

        if ($fcmToken === null || $fcmToken === '') {
            return response()->json(['status' => false, 'message' => 'FCM token is required'], 422);
        }

        $userModel = User::find($id);
        if (! $userModel) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        $userModel->user_token = $fcmToken;
        if ($userModel->save()) {
            return response()->json(['status' => true, 'status_text' => 'success', 'message' => 'Token updated successfully..'], 200);
        }

        return response()->json(['status' => false, 'message' => 'Failed'], 403);
    }

    // Send Push Notification
    public function sendNotification($message, $token, $from, $to)
    {
        // $url = 'https://fcm.googleapis.com/v1/projects/sntc-73467/messages:send'

        // $notif = [
        //     "message" => [
        //         "token" => "cGzzg20-RwOJ-1HnD5sfaO:APA91bHASbUPacqon9gT3G93vqa10TPBeky599w8lSw5D5KYUT1SXmFq_2iEpArVaMm4eB4-PP-Fs-1hE82JEW3y1k53yhMRPkmZSLTGMG1B-XzFUtyvdiJwA8JDSZ1P2Y2JFRfwGXcd",
        //         "data" => [
        //             "body" => "Body of Your Notification in data",
        //             "title" => "Title of Your Notification in data",
        //             "key_1" => "Value for key_1",
        //             "key_2" => "Value for key_2"
        //         ]
        //     ]
        // ];


        // $ch = curl_init();

        // curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/sntc-73467/messages:send');
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n\"message\": {\n\"token\": \"cGzzg20-RwOJ-1HnD5sfaO:APA91bHASbUPacqon9gT3G93vqa10TPBeky599w8lSw5D5KYUT1SXmFq_2iEpArVaMm4eB4-PP-Fs-1hE82JEW3y1k53yhMRPkmZSLTGMG1B-XzFUtyvdiJwA8JDSZ1P2Y2JFRfwGXcd\",\n\"data\": {\n\"body\": \"Body of Your Notification in data\",\n\"title\": \"Title of Your Notification in data\",\n\"key_1\": \"Value for key_1\",\n\"key_2\": \"Value for key_2\"\n}\n}\n}");

        // $headers = array();
        // $headers[] = 'Authorization: Bearer <Access token>';
        // $headers[] = 'Content-Type: application/json';
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // $result = curl_exec($ch);
        // if (curl_errno($ch)) {
        //     echo 'Error:' . curl_error($ch);
        // }
        // curl_close($ch);




        // return false;







        $url = "https://fcm.googleapis.com/fcm/send";
        // $token = "cGzzg20-RwOJ-1HnD5sfaO:APA91bHASbUPacqon9gT3G93vqa10TPBeky599w8lSw5D5KYUT1SXmFq_2iEpArVaMm4eB4-PP-Fs-1hE82JEW3y1k53yhMRPkmZSLTGMG1B-XzFUtyvdiJwA8JDSZ1P2Y2JFRfwGXcd";
        $serverKey = 'AAAA10hB_8I:APA91bHVSnAJjacznL6i3p9dWnKvJeceYJlTbwt_rvyq6Nx8tOPsMlxtYPqHzAJRAazC5JJof9PZHaw_uo1qbNkKK4YgJLKN_39ozcIlbCpt3YQ36Y5rT6ftegC0nnEiOZ-dYsYqFWcV';
        $title = "Message";
        $body = $message;

        $notification = [
            'title' => $title,
            'from' => $from,
            'to' => $to,
            'data' => ['messageFrom' => $from],
            'body' => $body,
            'sound' => 'default',
            'badge' => '1'
        ];

        $arrayToSend = [
            'to' => $token,
            'notification' => $notification,
            'data' => ['messageFrom' => $from],
            'priority' => 'high'
        ];

        $json = json_encode($arrayToSend);

        $headers = [];
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key=' . $serverKey;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if ($response === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $response;
    }

    // Save Message
    public function saveMessage(Request $request)
    {
        $userTo = User::where('id', $request->to)->where('userType' , 1)->first();
        $required = ['from', 'to', 'message'];
        $response = self::apiValidation($request->all(), $required);
        if ($response == null) {

            $messageModel = new Message;
            $messageModel->from = $request->from;
            $messageModel->to = $request->to;
            $messageModel->seen = 0;
            $messageModel->message = $request->message;
            $messageModel->status = $request->status ?: 0;
            $messageModel->save();

            $result = self::sendNotification($request->message, $userTo->user_token, $request->from, $request->to);

            if ($messageModel->id > 0) {
                return response()->json([
                    'test_user' => json_encode($userTo),
                    'status' => 'success',
                    'message' => $request->message,
                    'from' => $request->from,
                    'to' => $request->to,
                    'token' => $userTo->user_token,
                    'FirebaseResponse' => json_encode($result)
                ], 200);
            }
        } else {
            return response()->json($response, 403);
        }
    }

    public function getUserMessageCount($userId)
    {
        $message = Message::where(['to' => $userId])->where(['seen' => 0])->get()->count();
        return response()->json(['status' => 'success', 'data' => $message], 200);
    }

    public function getMessagesByIds($from, $to)
    {
        Message::where(['from' => $from, 'to' => $to])->orWhere(['from' => $to, 'to' => $from])->update(['seen' => 1]);
        // Message::where(['from' => $to ,'to' => $from])->update(['seen' => 1]);

        $userMessageData = Message::where(['from' => $to, 'to' => $from])->orWhere(function ($query) use ($from, $to) {
            return $query->where(['from' => $from, 'to' => $to]);
        })->orderBy('created_at', 'ASC')->get()->groupBy(function ($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d');
        });

        $getId = ($from == 1) ? $to : $from;

        $userId = ($from == 1) ? $to : $from;

        $user = User::select(['id', 'name', 'email', 'mobile', 'user_token'])->where('id', $getId)->first();
        if ($userMessageData->count() == 0) {
            $messageModel = new Message;
            $messageModel->from = 1;
            $messageModel->to = $userId;
            $messageModel->seen = 1;
            $messageModel->message = 'Welcome to SNTC chat support. How may we help you today ?';
            $messageModel->status = 0;
            $messageModel->save();

            $userMessageData = Message::where(['from' => $to, 'to' => $from])->orWhere(function ($query) use ($from, $to) {
                return $query->where(['from' => $from, 'to' => $to]);
            })->orderBy('created_at', 'ASC')->get()->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d');
            });
        }

        return response()->json(['status' => 'success', 'from' => $user, 'data' => $userMessageData], 200);
    }

    public function getMessageContacts()
    {
        $data = [];

        // $users = Message::orderBy('created_at','DESC')->has('user_rel')->with(['user_rel'=>function($query){
        //     return $query->select(['id' , 'name','email'])->get();
        // }])->groupBy('from')->get();

        $users = Message::orderBy('created_at', 'DESC')->where('from', '!=', 1)->has('user_rel')->with(['user_rel' => function ($query) {
            return $query->select(['id', 'name', 'email'])->get();
        }])->whereDate('created_at', '>', Carbon::now()->subDays(30)->format('Y-m-d'))
            ->whereIn(DB::raw("CONCAT(`from`, created_at)"), function ($query) {
                $query->select(DB::raw("CONCAT(`from`, MAX(created_at)) as hdate"))
                    ->from('messages')
                    ->groupBy('from');
            })
            ->get();


        return response()->json(['status' => 'success', 'data' => $users], 200);

        dd($users);
        $arrayUniqueUsers = array_unique($users);
        // dd($arrayUniqueUsers);

        foreach ($arrayUniqueUsers as $key => $user) {
            if ($user != 1) {
                // $data[][$user]['user'] = $user;
                $userDetails = User::find($user);
                if ($userDetails) {
                    $unseenMessage1 = Message::where(['from' => 1, 'to' => $user])->where('seen', 0)->orWhere(function ($query) use ($user) {
                        return $query->where(['from' => $user, 'to' => 1])->where('seen', 0);
                    })->get()->count();
                    // $unseenMessage2 = Message::where('to','=',$user)->where('seen' ,0)->get()->count();

                    $message = Message::where('from', '=', $user)->orWhere('to', '=', $user)->latest()->first(['message', 'created_at']);
                    $data[] = ['user' => $user, 'name' => $userDetails->name, 'email' => $userDetails->email, 'companyname' => $userDetails->companyname, 'last_message' => "hello", 'unseenMessage' => 0];
                }
            }
        }
        return response()->json(['status' => 'success', 'data' => $data], 200);
    }

    public function getMessageContactsRefator()
    {   // Created By Jaskaran To Refactor Code and tests 
        $messageWithUser = Message::where('from', '!=', 1)->with('user_rel')
            ->orderBy('id', 'DESC')->get()->unique('from');
        $newColl = $messageWithUser->mapToDictionary(function ($query) {
            $messageDetails = $this->getMessgaeDetails($query->user_rel->id);
            return [
                $query->user_rel->id => [
                    'user' =>   $query->user_rel->id,
                    'name' =>   $query->user_rel->name,
                    'email' =>  $query->user_rel->email,
                    'companyname' => $query->user_rel->companyname,
                    'last_message' => $messageDetails['latestMessage'],
                    'unseenMessage' => $messageDetails['unseenMessage']
                ]
            ];
        });
        return response()->json(['status' => 'success', 'data' => $newColl], 200);
    }

    public function getMessgaeDetails($userId)
    {
        return [
            'unseenMessage' => Message::where('from', '=', $userId)->where('seen', 0)->get()->count(),
            'latestMessage' => Message::where('from', '=', $userId)->orWhere('to', '=', $userId)->latest()->first()->message
        ];
    }

    public function checkUserExpired(Request $request, $userId)
    {
        $isExpiry = false;
        $sessionExpired = false;
        $sessionVersion = 0;
        $user = User::where('id', $userId)->where('userType' , 1)->first();
        $today = Carbon::now();
        $todayDate = $today->format('Y-m-d');
        if ($user != null) {
            $sessionVersion = (int) ($user->session_version ?? 0);
            $sessionExpired = $this->isAppSessionExpired($request, $user);
            if ($user->expired_on != null) {
                if ($user->expired_on > $todayDate) {
                    $isExpiry = false;
                } else {
                    $isExpiry = true;
                }
            } else {
                $isExpiry = false;
            }
        }
        return response()->json([
            'status' => true,
            'data' => $user ? $user->expired_on : null,
            'isExpiry' => $isExpiry,
            'session_expired' => $sessionExpired,
            'session_version' => $sessionVersion,
        ]);
    }

    public function getPriceStates()
    {

        $lastRecord = LivePrice::where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('id', 'DESC')->first();

        if ($lastRecord != null) {
            $lastDate = Carbon::parse($lastRecord->created_at)->format('Y-m-d');

            $prices = LivePrice::whereDate('created_at', $lastDate)->where('min_price', '!=', null)->where('max_price', '!=', null)->with([
                'name_rel' => function ($query) {
                    return $query->get();
                },
                'form_rel' => function ($query) {
                    return $query->orderBy('order', "ASC")->get();
                }
            ])->get()->groupBy('state');

            return response()->json(['status' => true, 'data' => $prices]);
        }
        return response()->json(['status' => true, 'data' => '']);
    }

    public function getTransportStates()
    {
        $port = Port::whereDate('created_at', Carbon::today()->format('Y-m-d'))->where('route', '!=', '0')->where('state_order', '!=', null)->where('price', '!=', '0')->get()->sortBy('state_order');
        if ($port->count() == 0) {
            $lastRecord = Port::orderBy('id', 'DESC')->where('route', '!=', '0')->where('price', '!=', '0')->orderBy('state_order')->first();
            $lastCreatedDate = $lastRecord->created_at;

            $port = Port::whereDate('created_at', $lastCreatedDate)->where('route', '!=', '0')->where('state_order', '!=', null)->where('price', '!=', '0')->get()->sortBy('state_order');
        }

        $sortedArray = [];
        $port = $port->groupBy('state');
        foreach ($port as $k => $v) {
            $sortedArray[] = [$k => $v];
        }
        return response()->json(['status' => true, 'data' => $sortedArray]);
    }

    public function getPortDetails($state)
    {
        $lastUpdatedDate = Port::orderBy('id', 'DESC')->first();

        if ($lastUpdatedDate != null) {
            $lastDate = ($lastUpdatedDate->created_at)->format('Y-m-d');
        }

        $port = Port::whereDate('created_at', $lastDate)->where('state', $state)->where('price', '!=', '0')->where('route', '!=', '0')->get();
        $portImage = PortImages::where('port', $state)->first();

        return response()->json(['status' => true, 'data' => $port, 'portImage' => $portImage]);
    }

    public function getUserPlan($userId)
    {
        $order = Order::where('user_id', $userId)->whereDate('end_date', '>=', Carbon::now()->format('Y-m-d'))->get();
        return response()->json(['status' => true, 'data' => $order]);
    }

    public function getAllBasmatiPrice($state)
    {
        $ricetype = 'basmati';
        $processedData = [];
        $lastRecord = LivePrice::where('name', '!=', '0')->where('form', '!=', '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('id', 'DESC')->first();

        if ($lastRecord != null) {
            $prices = LivePrice::where('name', '!=', '0')->where('form', '!=', '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->with([
                'name_rel' => function ($query) use ($ricetype) {
                    return $query->where('type', $ricetype)->get();
                },
                'form_rel' => function ($query) use ($ricetype) {
                    return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
                }
            ])->where('state', $state)->whereDate('created_at', $lastRecord->created_at->format('Y-m-d'))->get();
            $lastToLastDate = LivePrice::where('name', '!=', '0')->where('form', '!=', '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('created_at', 'DESC')
                ->whereDate('created_at', '<', $lastRecord->created_at->format('Y-m-d'))->get();

            if (!$lastToLastDate->isEmpty()) {

                $data = LivePrice::where('min_price', '!=', null)->where('max_price', '!=', null)->with([
                    'name_rel' => function ($query) {
                        // return $query->orderBy('order', 'asc')->get();
                        return $query->get();
                    },
                    'form_rel' => function ($query) use ($ricetype) {
                        return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
                    }
                ])->where(['state' => $state])->where(
                    DB::raw('date(created_at)'),
                    $lastRecord->created_at->format('Y-m-d')
                )->get();

                foreach ($data->sortBy('name_rel.order') as $k => $v) {
                    if ($v->name_rel != null && $v->state != null && $v->form_rel != null) {
                        if ($state == $v->state || strtoupper($state) == $v->state) {
                            $replaceHignfn = explode('-', $v->name_rel->type);
                            $implodeUnderscore = implode('_', $replaceHignfn);
                            $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                        }
                    }
                }

                $fiilteredProcessedData = [];
                foreach ($data->sortBy('form_rel.order') as $k => $v) {
                    if ($v->name_rel != null && $v->state != null && $v->form_rel != null) {
                        if ($state == $v->state || strtoupper($state) == $v->state) {
                            $replaceHignfn = explode('-', $v->name_rel->type);
                            $implodeUnderscore = implode('_', $replaceHignfn);
                            $fiilteredProcessedData[$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                        }
                    }
                }
                $newProcessed = [];
                foreach ($processedData as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $processedData[$k][$kk] = $fiilteredProcessedData[$kk];
                    }
                }
                $latstRecord = $lastRecord->created_at->format('Y-m-d');

                $newProcessedData = [];
                foreach ($processedData as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $key => $value) {
                            if (is_array($value)) {
                                foreach ($value as $ke => $val) {
                                    if (!array_key_exists($latstRecord, $val)) {
                                        unset($processedData[$k][$key][$ke]);
                                    }
                                }
                            }
                        }
                    }
                }


                foreach ($processedData as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $key => $val) {
                            if (empty($val)) {
                                unset($processedData[$k][$key]);
                            } else {
                                foreach ($val as $kk => $vv) {
                                    // dd($processedData[$k][$key][$kk] );
                                    if ($kk != 0) {
                                        $processedData[$k][$key][$kk]['isHide'] = 'true';
                                    }
                                }
                            }
                        }
                    }
                }

                $newProccessedData = [];

                $newData = collect($processedData)->map(function ($item) {
                    return collect($item)->map(function ($innerItem) use ($item) {
                        $onlyValues = array_values($innerItem);
                        $onlyKeys = array_keys($innerItem);
                        foreach ($onlyValues as $k => $v) {
                            if ($k == 0) {
                                $onlyValues[$k]['is_hide'] = 'false';
                            } else {
                                $onlyValues[$k]['is_hide'] = 'true';
                            }
                        }


                        $data = array_combine($onlyKeys, $onlyValues);
                        return $data;
                    });
                })->toArray();

                $order = [];
                foreach ($newData as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $order[$k][] = [$kk => $vv];
                    }
                }
                $myNewData = [];
                foreach ($order as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $newDataProcess = [];
                        foreach ($vv as $key => $value) {
                            foreach ($value as $ke => $val) {
                                $newDataProcess[] = [$ke => $val];
                            }
                            $myNewData[$k][$kk][$key] = $newDataProcess;
                        }
                    }
                }

                return response()->json([
                    'errors' => null,
                    'prices' => $myNewData,
                    'latest' => $lastRecord->created_at->format('Y-m-d'),
                    'oldDate' => $lastToLastDate[0]->created_at->format('Y-m-d')
                ]);
            }

            foreach ($prices as $k => $v) {
                if ($v->name_rel != null && $v->state != null && $v->form_rel != null) {
                    if ($state == $v->state) {
                        $replaceHignfn = explode('-', $v->name_rel->type);
                        $implodeUnderscore = implode('_', $replaceHignfn);
                        $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                    }
                }
            }
            dd("jhuijnk");
            return response()->json([
                'errors' => null,
                'prices' => json_encode($processedData),
                'latest' => $lastRecord->created_at->format('Y-m-d'),
                'oldDate' => ''
            ]);
        }
    }

    public function getAllNONBasmatiPrice($state)
    {
        $ricetype = 'non-basmati';
        $processedData = [];
        $lastRecord = LivePrice::where('name', '!=', '0')->where('form', '!=', '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('id', 'DESC')->first();

        if ($lastRecord != null) {
            $prices = LivePrice::where('name', '!=', '0')->where('form', '!=', '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->with([
                'name_rel' => function ($query) use ($ricetype) {
                    return $query->where('type', $ricetype)->get();
                },
                'form_rel' => function ($query) use ($ricetype) {
                    return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
                }
            ])->where('state', $state)->whereDate('created_at', $lastRecord->created_at->format('Y-m-d'))->get();
            $lastToLastDate = LivePrice::where('name', '!=', '0')->where('form', '!=', '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('created_at', 'DESC')
                ->whereDate('created_at', '<', $lastRecord->created_at->format('Y-m-d'))->get();

            if (!$lastToLastDate->isEmpty()) {

                $data = LivePrice::where('min_price', '!=', null)->where('max_price', '!=', null)->with([
                    'name_rel' => function ($query) {
                        // return $query->orderBy('order', 'asc')->get();
                        return $query->get();
                    },
                    'form_rel' => function ($query) use ($ricetype) {
                        return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
                    }
                ])->where(['state' => $state])->where(
                    DB::raw('date(created_at)'),
                    $lastRecord->created_at->format('Y-m-d')
                )->get();

                foreach ($data->sortBy('name_rel.order') as $k => $v) {
                    if ($v->name_rel != null && $v->state != null && $v->form_rel != null) {
                        if ($state == $v->state || strtoupper($state) == $v->state) {
                            $replaceHignfn = explode('-', $v->name_rel->type);
                            $implodeUnderscore = implode('_', $replaceHignfn);
                            $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                        }
                    }
                }

                $fiilteredProcessedData = [];
                foreach ($data->sortBy('form_rel.order') as $k => $v) {
                    if ($v->name_rel != null && $v->state != null && $v->form_rel != null) {
                        if ($state == $v->state || strtoupper($state) == $v->state) {
                            $replaceHignfn = explode('-', $v->name_rel->type);
                            $implodeUnderscore = implode('_', $replaceHignfn);
                            $fiilteredProcessedData[$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                        }
                    }
                }
                $newProcessed = [];
                foreach ($processedData as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $processedData[$k][$kk] = $fiilteredProcessedData[$kk];
                    }
                }
                $latstRecord = $lastRecord->created_at->format('Y-m-d');

                $newProcessedData = [];
                foreach ($processedData as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $key => $value) {
                            if (is_array($value)) {
                                foreach ($value as $ke => $val) {
                                    if (!array_key_exists($latstRecord, $val)) {
                                        unset($processedData[$k][$key][$ke]);
                                    }
                                }
                            }
                        }
                    }
                }


                foreach ($processedData as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $key => $val) {
                            if (empty($val)) {
                                unset($processedData[$k][$key]);
                            } else {
                                foreach ($val as $kk => $vv) {
                                    // dd($processedData[$k][$key][$kk] );
                                    if ($kk != 0) {
                                        $processedData[$k][$key][$kk]['isHide'] = 'true';
                                    }
                                }
                            }
                        }
                    }
                }

                $newProccessedData = [];

                $newData = collect($processedData)->map(function ($item) {
                    return collect($item)->map(function ($innerItem) use ($item) {
                        $onlyValues = array_values($innerItem);
                        $onlyKeys = array_keys($innerItem);
                        foreach ($onlyValues as $k => $v) {
                            if ($k == 0) {
                                $onlyValues[$k]['is_hide'] = 'false';
                            } else {
                                $onlyValues[$k]['is_hide'] = 'true';
                            }
                        }


                        $data = array_combine($onlyKeys, $onlyValues);
                        return $data;
                    });
                })->toArray();

                $order = [];
                foreach ($newData as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $order[$k][] = [$kk => $vv];
                    }
                }
                $myNewData = [];
                foreach ($order as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $newDataProcess = [];
                        foreach ($vv as $key => $value) {
                            foreach ($value as $ke => $val) {
                                $newDataProcess[] = [$ke => $val];
                            }
                            $myNewData[$k][$kk][$key] = $newDataProcess;
                        }
                    }
                }

                return response()->json([
                    'errors' => null,
                    'prices' => $myNewData,
                    'latest' => $lastRecord->created_at->format('Y-m-d'),
                    'oldDate' => $lastToLastDate[0]->created_at->format('Y-m-d')
                ]);
            }
        }
    }
    public function getAllStateList()
    {
        $lastRecord = LivePrice::where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('id', 'DESC')->first();

        if ($lastRecord != null) {
            $lastDate = Carbon::parse($lastRecord->created_at)->format('Y-m-d');

            $prices = LivePrice::whereDate('created_at', $lastDate)->where('min_price', '!=', null)->where('max_price', '!=', null)->with([
                'name_rel' => function ($query) {
                    return $query->get();
                },
                'form_rel' => function ($query) {
                    return $query->orderBy('id', "ASC")->get();
                }
            ])->get()->groupBy('state');
            $array_keys = array_keys($prices->toArray());


            return response()->json(['status' => 'success', 'data' => $array_keys]);
        }
        return response()->json(['status' => true, 'data' => '']);



        $livePrice = LivePrice::orderBy('state_order')->where('min_price', '!=', null)->where('max_price', '!=', null)->select('state')->get()->groupBy('state');
        // dd($livePrice);
        $array_keys = array_keys($livePrice->toArray());
        return response()->json(['status' => 'success', 'data' => $array_keys]);
    }
    public function getPricesByState($state = 'PUNJAB-HARYANA')
    {
        $lastPrice  = LivePrice::last();
        // dd($lastPrice);
    }

    public function getPortsInOrder()
    {
        try {

            $lastDate = Port::where('price', '!=', 0)->orderByDesc('id')->first('created_at');
            $ports    = Port::where('price', '!=', 0)->where('created_at', 'LIKE', '%' . $lastDate->created_at . '%')
                ->orderBy('state_order')->get()->groupBy(['state', 'route']);

            return response()->json(['status' => true, 'data' => $ports]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getLatestAndroidVersion()
    {
        $version  = Version::orderBy('id', 'desc')->first();
        return response()->json(['status' => 'success', 'data' => $version]);
    }

    public function getOceanFreight()
    {
        $oceanfreight = OceanFreight::get();
        dd($oceanfreight);
        return response()->json(['status' => 'success', 'data' => $oceanfreight]);
    }

    public function getUSDPrices_old($userId)
    {
        // ht1901
        $fiftykgbgids = USD_defaultmaster::select('id')->where('bag_size', '50kg')->get()->map(function ($query) {
            return $query->id;
        })->toArray();
        $usdPrices = USD_prices::groupBy('rice')->whereIn('usd_defaultMaster_id', $fiftykgbgids)->get()->map(function ($query) {
            return $query->rice;
        });

        $riceArray = $usdPrices->toArray();
        $usdData = collect();
        foreach ($riceArray as $key => $value) {
            $usdData[] = USD_prices::where('rice', $value)->whereIn('usd_defaultMaster_id', $fiftykgbgids)->orderBy('id', 'desc')->first();
        }

        // dd($usdPrices);
        // dd($riceArray);
        // dd($usdPrices->toArray());


        $getUSDPrices = USD_prices::select('created_at')->where('status', 1)->orderBy('id', 'desc')->first();
        $latestDateforQuery = $getUSDPrices->created_at->format('Y-m-d');
        $latestDate = $getUSDPrices->created_at->format('d-M-Y, g:i A');


        // $usdData = USD_prices::with(['getRiceQuality','getUSDDefaultMaster' => function($query) {
        //     return $query->where('bag_size' , '50Kg')->get();
        // }])->where('status' , 1)->get();



        // $usdData = USD_prices::with(['getRiceQuality','getUSDDefaultMaster' => function($query) {
        //     return $query->where('bag_size' , '50Kg')->get();
        // }])->where('status' , 1)->orderBy('created_at' , 'ASC')->get();


        $basmatiData = [];
        $nonbasmatiData = [];
        $zeroValueRice = [];

        foreach ($usdData as $k => $v) {
            if ($v->ricemin != 0) {
                if ($v->getUSDDefaultMaster != null) {
                    $stringFob = $v->fobmin;
                    $stringFobMax = $v->fobmax;
                    unset($v['fobmin']);
                    unset($v['fobmax']);

                    $v['fobmin'] = floatval($stringFob);
                    $v['fobmax'] = floatval($stringFobMax);

                    if ($v->getRiceQuality->quality_type == 'basmati') {
                        $basmatiData[$v->getRiceQuality->order][$v->rice] = $v;
                    } else {

                        $nonbasmatiData[$v->getRiceQuality->order][$v->rice] = $v;
                    }
                }
            } else {
                $zeroValueRice[] = $v['rice'];
            }
        }

        // $distinctUSD = USD_prices::with(['getRiceQuality' , 'getUSDDefaultMaster' => function($query) {
        //     return $query->where('bag_size' , '50Kg')->get();
        // }])->where('status' , 1)->orderBy('created_at' , 'DESC')->get()->map( function($query) {
        //     if( $query->getUSDDefaultMaster != null ){
        //         return $query;
        //     }
        // });

        // $basmatiPrices = [];
        // $nonbasmatiPrices = [];

        // foreach( $distinctUSD as $k => $v ){
        //     if( $v != null ){
        //         if( $v->getUSDDefaultMaster != null ){

        //             $valuableData = [];
        //             $value = $v->toArray();

        //             $get_rice_quality = $value['get_rice_quality'];
        //             $fobminString = $value['fobmin'];
        //             $fobmaxString = $value['fobmax'];

        //             unset($value['get_rice_quality']);
        //             unset($value['fobmin']);
        //             unset($value['fobmax']);

        //             $valuableData = $value;
        //             $valuableData['quality'] = $get_rice_quality['quality']; 
        //             $valuableData['quality_name'] = $get_rice_quality['quality_name']; 
        //             $valuableData['fobmin'] = round($fobminString , 2); 
        //             $valuableData['fobmax'] = round($fobmaxString , 2); 

        //             if( $get_rice_quality['quality_type_status'] == 1 ){
        //                 $basmatiPrices[$valuableData['rice']] = $valuableData;
        //             }else{
        //                 $nonbasmatiPrices[$valuableData['rice']] = $valuableData;
        //             }
        //         }
        //     }

        // }

        $defalutPort = "Jebel Ali";
        $userData = User::where('id', $userId)->where('userType' , 1)->first();

        if ($userData->import_port != null && $userData->import_port != '') {
            $defalutPort = $userData->import_port;
        }

        $defalutPortDetail = OceanFreight::where('port', $defalutPort)->get();
        if ($defalutPortDetail->count() > 0) {
            $defalutPortPrice = $defalutPortDetail[0]['freight_25MT_1MT'];
        }
        ksort($basmatiData);
        ksort($nonbasmatiData);

        $basData = [];
        $nonBasData = [];
        foreach ($basmatiData as $k => $v) {
            foreach ($v as $kk => $vv) {
                $basData[] = $vv;
            }
        }

        foreach ($nonbasmatiData as $k => $v) {
            foreach ($v as $kk => $vv) {
                $nonBasData[] = $vv;
            }
        }


        return response()->json(['status' => true, 'basmatiPrices' => $basData, 'nonbasmatiPrices' => $nonBasData, 'defaultCIFPrice' => floatval($defalutPortPrice), 'latestDate' => $latestDate, 'defalutPort' => $defalutPort, 'test' => 1]);
    }

    public function getUSDPrices($userId)
    {
        $fiftykgbgids = USD_defaultmaster::query()
            ->select('id')
            ->where('bag_size', '50kg')
            ->pluck('id')
            ->toArray();


        $latestRecords = USD_prices::whereIn('usd_defaultMaster_id', $fiftykgbgids)
            ->select('id', 'rice', \DB::raw('MAX(id) as max_id'))
            ->groupBy('rice');

        $usdData = USD_prices::join(\DB::raw("({$latestRecords->toSql()}) as latest_records"), function ($join) {
            $join->on('USD_prices.id', '=', 'latest_records.max_id');
        })
            ->mergeBindings($latestRecords->getQuery())
            ->select('USD_prices.*')
            ->orderBy('USD_prices.rice', 'ASC')
            ->orderBy('USD_prices.id', 'DESC')
            ->get();



        $getUSDPrices = USD_prices::select('created_at')->where('status', 1)->latest('id')->first();
        $latestDate = $getUSDPrices->created_at->format('d-M-Y, g:i A');

        $basmatiData = [];
        $nonbasmatiData = [];
        $zeroValueRice = [];

        foreach ($usdData as $k => $v) {
            if ($v->ricemin != 0) {
                if ($v->getUSDDefaultMaster != null) {
                    $stringFob = $v->fobmin;
                    $stringFobMax = $v->fobmax;
                    unset($v['fobmin']);
                    unset($v['fobmax']);

                    $v['fobmin'] = floatval($stringFob);
                    $v['fobmax'] = floatval($stringFobMax);

                    if ($v->getRiceQuality->quality_type == 'basmati') {
                        $basmatiData[$v->getRiceQuality->order][$v->rice] = $v;
                    } else {

                        $nonbasmatiData[$v->getRiceQuality->order][$v->rice] = $v;
                    }
                }
            } else {
                $zeroValueRice[] = $v['rice'];
            }
        }

        $defalutPort = "Jebel Ali";
        $userData = User::where('id', $userId)->where('userType' , 1)->first();

        if ($userData->import_port != null && $userData->import_port != '') {
            $defalutPort = $userData->import_port;
        }

        $defalutPortDetail = OceanFreight::where('port', $defalutPort)->get();
        if ($defalutPortDetail->count() > 0) {
            $defalutPortPrice = $defalutPortDetail[0]['freight_25MT_1MT'];
        }
        ksort($basmatiData);
        ksort($nonbasmatiData);

        $basData = [];
        $nonBasData = [];
        foreach ($basmatiData as $k => $v) {
            foreach ($v as $kk => $vv) {
                $basData[] = $vv;
            }
        }

        foreach ($nonbasmatiData as $k => $v) {
            foreach ($v as $kk => $vv) {
                $nonBasData[] = $vv;
            }
        }


        return response()->json(['status' => true, 'basmatiPrices' => $basData, 'nonbasmatiPrices' => $nonBasData, 'defaultCIFPrice' => floatval($defalutPortPrice), 'latestDate' => $latestDate, 'defalutPort' => $defalutPort, 'test' => 1]);
    }

    public function USDOceanFreight()
    {
        $oceanfreight = OceanFreight::get();
        dd($oceanfreight);
    }

    public function getDistinctRegion()
    {
        $oceanFreight = OceanFreight::where('freight_21MT', '!=', 0)->get()->groupBy('region')->map(function ($query) {
            return $query->groupBy('country');
        })->toArray();

        return response()->json(['status' => true, 'region' => array_keys($oceanFreight), 'data' => $oceanFreight]);
    }

    public function getAllPorts($riceQualityId, $userId)
    {

        $chartUSDPrice = USD_prices::with(['getRiceQuality', 'getUSDDefaultMaster' => function ($query) {
            return $query->where('bag_size', '50kg')->get();
        }])->orderBy('created_at', 'DESC')->where('ricemin', '!=', 0)->where('ricemax', '!=', 0)->where('rice', $riceQualityId)->get();
        // dd($chartUSDPrice);
        // $hasRiceType = $chartUSDPrice->getRiceQuality;



        // $chartUSDPrice = USD_prices::with(['getRiceQuality', 'getUSDDefaultMaster'])->orderBy('created_at' , 'DESC')->where('rice' , $riceQualityId)->get();

        $date = [];
        $prices = [];
        $combinedData = [];
        $usdDefaultMasterId = '';
        foreach ($chartUSDPrice as $k => $v) {
            if (isset($v->getUSDDefaultMaster)) {
                if ($v->getUSDDefaultMaster !=  null) {
                    $usdDefaultMasterId = $v->usd_defaultMaster_id;
                    if (!array_key_exists(strtotime($v->created_at) . "000", $combinedData)) {
                        $date[] = strtotime($v->created_at) . "000";
                        $prices[] = $v->fobmax;
                        $combinedData[strtotime($v->created_at) . "000"] = [(int)((strtotime($v->created_at)) * 1000), (int)($v->fobmax)];
                    }
                }
            }
        }

        $chartData = ['date' => $date, 'prices' => $prices, 'combinedData' => array_values($combinedData)];
        $defalutPort = "Jebel Ali";

        $userData = User::where('id', $userId)->where('userType' , 1)->first();

        if ($userData->import_port != null && $userData->import_port != '') {
            $defalutPort = $userData->import_port;
        }
        $defalutPortDetail = OceanFreight::where('port', $defalutPort)->get();

        if ($defalutPortDetail->count() > 0) {
            $defalutPortPrice = $defalutPortDetail[0]['freight_25MT_1MT'];
        }

        $riceQualityIdDetails = QualityMaster::where('id', $riceQualityId)->first();

        if ($riceQualityIdDetails['quality_type_status'] == 2) {
            $newAppliedFor = '1';
        } else {
            $newAppliedFor = '0';
        }
        if ($riceQualityIdDetails['quality_type'] == "non-basmati") {
            $quality_type_status = 1;
        } else {
            $quality_type_status = 0;
        }
        $oceanPorts = OceanFreight::where('freight_21MT', '!=', 0)->get()->groupBy('region')->map(function ($query) {
            return $query->groupBy('country')->map(function ($query2) {
                return $query2->groupBy('port');
            });
        });
        $PMT_data = USD_defaultmaster::where('bag_size', 'like', '50Kg')->where('applied_for', $newAppliedFor)->first();

        $getUSDPrices = USD_prices::where('rice', $riceQualityId)->where('ricemin', '!=', 0)->where('ricemax', '!=', 0)->where('usd_defaultMaster_id', $PMT_data->id)->orderBy('id', 'desc')->first();
        // $getUSDPrices = USD_prices::where('rice' , $riceQualityId )->where('ricemin' , '!=' , 0)->where('ricemax' , '!=' , 0)->where('status' , 1)->orderBy('id' , 'desc')->first();


        $latestDate = $getUSDPrices->created_at->format('d-M-Y, g:i A');

        $USD_fiftykg_master = USD_defaultmaster::select('id', 'bag_size', 'bag_type', 'PMT_USD')->where('applied_for', $newAppliedFor)->where('bag_size', 'like', '50Kg')->orderBy('created_at', 'desc')->first();

        $usdDefaultMaster = USD_defaultmaster::select('bag_size', 'bag_type', 'id', 'PMT_USD')->orderBy('order', 'ASC')->where('applied_for', $quality_type_status)->get();
        $usdDefaultMasterArray = $usdDefaultMaster->toArray();
        // dd($usdDefaultMasterArray);
        //             $updatedArray = [];
        //             foreach($usdDefaultMasterArray  as $k => $v){
        //                 $updatedArray[$usdDefaultMasterArray[$k]['id']] = $v;
        //             }
        // dd($updatedArray);
        return response()->json(['status' => true, 'ports' => $oceanPorts->toArray(), 'packing' => $usdDefaultMasterArray, 'riceQuality' => $riceQualityIdDetails, 'PMT_data' => $PMT_data, 'FOB' => $getUSDPrices, 'fiftykgMaster' => $USD_fiftykg_master, 'defalutPortPrice' => $defalutPortPrice, 'defalutPort' => $defalutPort, 'chartData' => $chartData, 'defalutPortDetail' => $defalutPortDetail]);
    }

    public function getQualityDetails($id)
    {
        $qualityMaster = QualityMaster::where('id', $id)->get();

        if ($qualityMaster->count() > 0) {
            $qualityType = $qualityMaster[0]['quality_type'];
            $qualityTypeStatus = $qualityMaster[0]['quality_type_status'];
            $usdDefaultMaster = USD_defaultmaster::select('id', 'bag_size', 'bag_type', 'PMT_USD')->where('applied_for', $qualityTypeStatus);

            $usdDefaultData = $usdDefaultMaster->get();
            $fiftykgBagData = $usdDefaultMaster->where('bag_size', 'like', '50Kg')->first();
            $fiftykgBagPMT = $fiftykgBagData->PMT_USD;

            $usdPrices = USD_prices::where('rice', $qualityMaster[0]->id)->orderBy('created_at', 'DESC')->get();
            $fobmin = '';
            $fobmax = '';
            if ($usdPrices->count() > 0) {
                $lastUpdatedDate = $usdPrices[0]->created_at;
                $fobmin = $usdPrices[0]['fobmin'];
                $fobmax = $usdPrices[0]['fobmax'];
            }
            $processedData = [];
            foreach ($usdDefaultData as $k => $v) {
                $v['fobmin'] = (round($fobmin, 2) - round($fiftykgBagPMT, 2) + round($v['PMT_USD'], 2));
                $v['fobmax'] = (round($fobmax, 2) - round($fiftykgBagPMT, 2) + round($v['PMT_USD'], 2));
                $processedData[] = $v->toArray();
            }

            dd($processedData);
        }
    }

    public function getAllPortsgetDataForBuyer()
    {
        return response()->json($this->buildDataForBuyerPayload());
    }

    /**
     * Secure web buyer data (Bearer / X-API-TOKEN). Requires ?type=basmati|non-basmati
     */
    public function getWebDataForBuyer(Request $request)
    {
        $type = strtolower(trim((string) $request->query('type', '')));
        if (! in_array($type, ['basmati', 'non-basmati'], true)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or missing type. Allowed values: basmati, non-basmati',
            ], 422);
        }

        return response()->json($this->buildDataForBuyerPayload($type));
    }

    private function buildDataForBuyerPayload(?string $type = null): array
    {
        $riceQualityMasterQuery = QualityMaster::select([
            'id', 'quality', 'quality_name', 'quality_type', 'quality_type_status', 'status', 'order',
        ]);
        $qualityMasterQuery = RiceName::query();

        if ($type !== null) {
            $riceQualityMasterQuery->where('quality_type', $type);
            $qualityMasterQuery->where('type', $type);
        }

        $riceQualityMaster = $riceQualityMasterQuery->get()->groupBy('quality_type');
        $qualityMaster = $qualityMasterQuery->get()->groupBy('type');
        $riceQualityDataArray = $qualityMaster->toArray();
        $riceQualityArray = $qualityMaster->count() ? array_keys($riceQualityDataArray) : [];

        $usdDefaultMasterArray = [];
        if ($type === null) {
            $usdDefaultMaster = USD_defaultmaster::get()->groupBy('applied_for')->toArray();
            foreach ($usdDefaultMaster as $k => $v) {
                if ($k == 1) {
                    $usdDefaultMasterArray['basmati'] = $v;
                } else {
                    $usdDefaultMasterArray['non-basmati'] = $v;
                }
            }
        } else {
            $appliedFor = $type === 'basmati' ? 1 : 0;
            $usdDefaultMasterArray[$type] = USD_defaultmaster::where('applied_for', $appliedFor)->get()->toArray();
        }

        $portArray = OceanFreight::select('id', 'region', 'country', 'port', 'freight_25MT')
            ->orderBy('port', 'ASC')
            ->where('port', '!=', '')
            ->get()
            ->toArray();

        $payload = [
            'status' => true,
            'riceQualityMasterArray' => $riceQualityMaster->toArray(),
            'riceQualityType' => $riceQualityArray,
            'riceQualityData' => $usdDefaultMasterArray,
            'ports' => $portArray,
            'riceQualityDataArray' => $riceQualityDataArray,
        ];

        if ($type !== null) {
            $payload['type'] = $type;
        }

        return $payload;
    }

    public function addRiceQuality(Request $request)
    {
        $validDate = Carbon::now()->addDays(10);
        $buyerQuery = BuyQuery::create([
            'PackingType' => $request->changePackingType,
            'mobile' => $request->mobile,
            'partyName' => $request->party,
            'portName' => $request->portName,
            'qualityName' => $request->quality,
            'quantity' => $request->quantity,
            'remarks' => $request->remarks,
            'validDays' => 10,
            'validDate' => $validDate,
            'qualityType' => $request->selectedQualityType,
            'user' => $request->user
        ]);

        $user = User::where('id', $request->user)->where('userType' , 1)->first();
        $queryData = BuyQuery::with('getPackingType')->where('id', $buyerQuery->id)->first();

        $data = ['country' => $user->country, 'username' =>  $user->name, 'email' => $user->email, 'mobile' => $user->mobile, 'query' => $queryData];

        $response = MailController::html_email('mailBuyQuery', 'enquiry@sntcgroup.com', 'enquiry@sntcgroup.com', $data);
        // $response = MailController::html_email('mailBuyQuery','rbajaj@sntcgroup.com','rbajaj@sntcgroup.com' , $data); 
        // $response = MailController::html_email('mailBuyQuery','vidula@sntcgroup.com','vidula@sntcgroup.com' , $data); 
        // $response = MailController::html_email('mailBuyQuery','leena@sntcgroup.com','leena@sntcgroup.com' , $data); 

        $listUser = User::whereIn('id', [4, 6])->where('userType' , 1)->get();
        $result = self::sendNotif("Notification", "Buyer Requirement", '', $buyerQuery->id);
        return false;

        foreach ($listUser as $k => $v) {
            $result = self::sendNotif("Notification", "Buyer Requirement", $v->user_token);
        }

        return response()->json(['data' => $request->all()]);
    }
    public function sendNotif($title, $message, $token, $buyerQuery)
    {
        $token = "c_kAXj3VQO6vUapdES8jMo:APA91bEe_kp_c-B0YcXCFWnwNd-9VzQ0BzKXOJEoSoKP5hxX3qKxGPSugmk6N_VIdurkWVQwSx26t5AlDSggaUWpvLGgPa-dgMSg-a9soUA67e6YipBs84pXQVoM5tzOh_t8W-5Hefbn";

        $url = "https://fcm.googleapis.com/fcm/send";
        $serverKey = 'AAAA10hB_8I:APA91bHVSnAJjacznL6i3p9dWnKvJeceYJlTbwt_rvyq6Nx8tOPsMlxtYPqHzAJRAazC5JJof9PZHaw_uo1qbNkKK4YgJLKN_39ozcIlbCpt3YQ36Y5rT6ftegC0nnEiOZ-dYsYqFWcV';

        // firebase setup on zipzap.seo@gmail.com
        $body = $message;
        $notification = ['title' => $title, 'body' => $body, 'sound' => 'default', 'badge' => '1', 'data' => 'here'];

        $apns = [
            'payload' =>
            [
                'aps' => [
                    'sound' => 'default',
                    'badge' => 1,
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
            ['notification_forground' => 'true'],
            'notification' => $notification,
            'priority' => 'high',
            'data' => ['buyerQuery' => $buyerQuery]
        ];


        $json = json_encode($arrayToSend);
        $headers = [];
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key=' . $serverKey;
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

    public function getBuyerDetails($id)
    {
        $buyerQuery = BuyQuery::where('id', $id)->first();
        return response()->json(['data' => $buyerQuery]);
    }

    public function saveBid(Request $request)
    {
        $bidPrice = $request->bidPrice;
        $queryDataId = $request->queryDataId;
        $user_id = $request->user_id;

        $userDetails = User::where('id', $user_id)->where('userType' , 1)->first();

        $bid =  Bid::create([
            'query_id' => $queryDataId,
            'seller_id' => $user_id,
            'bid_amount' => $bidPrice
        ]);

        $bidDetail = BuyQuery::where('id', $queryDataId)->first();

        $data = ['user' =>  $userDetails, 'bid' => $bidDetail];

        $response = MailController::html_email('mailbid', 'enquiry@sntcgroup.com', 'enquiry@sntcgroup.com', $data);
        // $response = MailController::html_email('mailbid','rbajaj@sntcgroup.com','rbajaj@sntcgroup.com' , $data); 
        // $response = MailController::html_email('mailbid','vidula@sntcgroup.com','vidula@sntcgroup.com' , $data); 
        // $response = MailController::html_email('mailbid','leena@sntcgroup.com','leena@sntcgroup.com' , $data); 


        // $response = MailController::html_email('mailbid','rbajaj@sntcgroup.com','rbajaj@sntcgroup.com'); 
        // $response = MailController::html_email('mailbid','vidula@sntcgroup.com','vidula@sntcgroup.com'); 
        // $response = MailController::html_email('mailbid','leena@sntcgroup.com','leena@sntcgroup.com'); 


        if ($bid) {
            return response()->json(['status' => true]);
        } else {
            return response()->json(['status' => false]);
        }
    }
    public function getCalculatorData()
    {
        $qualityMaster = QualityMaster::where('status', 1)->get();
        $defaultValues = Defaultvalue::orderBy('id', 'DESC')->first();
        $USD_fiftykg_master = USD_defaultmaster::select('id', 'bag_size', 'bag_type', 'PMT_USD')->where('bag_size', 'like', '50Kg')->orderBy('created_at', 'desc')->first();
        $USD_master = USD_defaultmaster::select('id', 'bag_size', 'bag_type', 'PMT_USD', 'bag_cost')->get();

        return response()->json(['status' => true, 'qualityMaster' => $qualityMaster, 'defaultValues' => $defaultValues, 'fiftykg' => $USD_fiftykg_master, 'USD_master' => $USD_master]);
    }
    public function saveUSDPrices(Request $request)
    {
        $category            = $request->category;
        $charges             = $request->charges;
        $dollarrate          = $request->dollarrate;
        $exchangeRatemax     = $request->exchangeRatemax;
        $exchangeRatemin     = $request->exchangeRatemin;
        $fobmax              = $request->fobmax;
        $fobmin              = $request->fobmin;
        $percentageValue     = $request->percentageValue;
        $rice                = $request->rice;
        $ricemax             = $request->ricemax;
        $ricemin             = $request->ricemin;
        $totalMax            = $request->totalMax;
        $totalMin            = $request->totalMin;
        $transportmax        = $request->transportmax;
        $transportmin        = $request->transportmin;
        $user_id             = $request->user_id;
        $usd_defaultMaster_id = $request->usd_defaultMaster_id;

        if ($request->usd_defaultMaster_id == 0 || $request->usd_defaultMaster_id == '0') {
            $usd_defaultMaster_id = 48;
        }

        USD_prices::create([
            'rice' => $rice,
            'ricemin' => $ricemin,
            'ricemax' => $ricemax,
            'transportmin' => $transportmin,
            'transportmax' => $transportmax,
            'category' => $category,
            'charges' => $charges,
            'dollarrate' => $dollarrate,
            'percentageValue' => $percentageValue,
            'totalMin' => $totalMin,
            'totalMax' => $totalMax,
            'exchangeRatemin' => $exchangeRatemin,
            'exchangeRatemax' => $exchangeRatemax,
            'fobmin' => $fobmin,
            'fobmax' => $fobmax,
            'status' => 1,
            'user_id' => $user_id,
            'usd_defaultMaster_id' => $usd_defaultMaster_id
        ]);

        return response()->json(['statue' => true]);
    }

    public function getMyBids($user_id)
    {
        $myBids = BuyQuery::with(['getPackingType', 'getBidsExtra' => function ($query) use ($user_id) {
            return $query->where('seller_id', '!=', $user_id)->where('counter_status', 1)->orWhere('accept_status', 1)->get();
        }, 'getBids' => function ($query) use ($user_id) {
            return $query->where('seller_id', $user_id)->orderBy('id', 'desc')->get();
            // return $query->orWhere('seller_id' , $user_id)->orWhere('counter_status' , 1)->orderBy('id' , 'desc')->get();
        }])->orderBy('id', 'DESC')->where('status', '!=', 0)->limit(100)->get();

        foreach ($myBids as $k => $v) {
            // $v['is_bid_accepted_by_me'] = 'false';
            if ($v['getBids']->count() > 0) {
                foreach ($v['getBids'] as $ke => $val) {

                    if (Carbon::now()->greaterThan(Carbon::parse($val->validTill))) {
                        $v['my_bid_expired'] = 'true';
                    } else {
                        $v['my_bid_expired'] = 'false';
                    }
                    // if( Carbon::parse($val->validTill)->format('d-m-Y') < Carbon::now()->format('d-m-Y') ){
                    //     $v['my_bid_expired'] = 'true';
                    // }else{
                    //     $v['my_bid_expired'] = 'false';
                    // }

                    $v['is_accepted_by_admin'] = 'false';
                    if ($user_id != $val['seller_id']) {
                        if ($val['counter_status'] == 1 || $val['accept_status'] == 1) {
                            $v['is_bid_closed'] = 'true';
                            $v['bid_closed_amount'] = $val['counter_amount'];
                        } else {
                            $v['is_bid_closed'] = 'false';
                        }
                    } else {
                        if ($val['counter_status'] == 1 && $user_id == $val['seller_id']) {
                            $v['is_bid_accepted_by_me'] = 'true';
                        }
                        if ($val['counter_status'] == 2 && $user_id == $val['seller_id']) {
                            $v['is_bid_accepted_by_me'] = 'false';
                        }
                        if ($val['counter_amount'] != 0 && $user_id == $val['seller_id'] && $val['counter_status'] == 0) {
                            $v['is_bid_accepted_by_me'] = 'pending';
                        }

                        if ($val['accept_status'] == 1) {
                            $v['is_accepted_by_admin'] = 'true';
                        }
                        $v['user_bid_amount'] = $val['counter_amount'];
                        $v['user_bid_date'] = $val['created_at'];
                    }
                }
                $val['validTill'] = date("Y-m-d\TH:i", strtotime($val['validTill']));
            }
            if (Carbon::parse($v->validDate)->format('Y-m-d H:i') < Carbon::now()->format('Y-m-d H:i')) {
                $v['is_expired'] = 'true';
            } else {
                $v['is_expired'] = 'false';
            }
        }
        return response()->json(['statue' => true, 'bids' => $myBids]);
    }

    public function saveUserBid(Request $request)
    {
        Bid::create(['query_id' => $request->buyQueryId, 'validTill' => Carbon::now()->addDays($request->validTill), 'seller_id' => $request->userid, 'bid_amount' => $request->amount, 'status' => 1]);

        $user = User::where('id', $request->userid)->where('userType' , 1)->first();
        $queryData = BuyQuery::where('id', $request->buyQueryId)->first();

        $data = ['id' => ($queryData->id + 1), 'username' =>  $user->name, 'email' => $user->email, 'mobile' => $user->mobile, 'query' => $queryData->qualityName, 'bidAmount' => $request->amount, 'validTill' => Carbon::now()->addDays($request->validTill)];

        $response = MailController::html_email('mailsupplieroffer', 'enquiry@sntcgroup.com', 'enquiry@sntcgroup.com', $data);
        // $response = MailController::html_email('mailsupplieroffer' ,'rbajaj@sntcgroup.com','rbajaj@sntcgroup.com',$data);
        // $response = MailController::html_email('mailsupplieroffer' ,'vidula@sntcgroup.com','vidula@sntcgroup.com',$data);
        // $response = MailController::html_email('mailsupplieroffer' ,'leena@sntcgroup.com','leena@sntcgroup.com',$data);

        $myBids = BuyQuery::with(['getBids' => function ($query) {
            return $query->orderBy('id', 'desc')->get();
        }])->orderBy('id', 'DESC')->get();

        return response()->json(['status' => true, 'data' => $myBids]);
    }

    public function getAllVendors()
    {
        $bagVendors = Vendorcategory::where('id', '!=', 8)->with(['getVendorList' => function ($query) {
            return $query->where('vendor_name', '!=', '')->get();
        }])->get()->groupBy('name');

        return response()->json(['status' => true, 'data' => $bagVendors]);
    }

    public function getUSDPlans()
    {
        $USDPlan = USDPlan::orderBy('id', 'DESC')->get();
        $DefaultValues = Defaultvalue::first();

        return response()->json(['status' => true, 'plans' => $USDPlan, 'DefaultValues' => $DefaultValues]);
    }
    public function getCountryList()
    {
        $countries = OceanFreight::get()->groupBy('country');

        return response()->json(['status' => true, 'countries' => $countries]);
    }
    public function getContactDetails()
    {
        $contactus = Contact::first();
        return response()->json(['status' => true, 'data' => $contactus]);
    }
    public function updateCounterStatus(Request $request)
    {
        // 1: accept , 2: reject
        Bid::where(['id'  =>  $request->bid_id])->update(['counter_status' => $request->counter_status]);
        $bidDetails = Bid::where(['id' => $request->bid_id])->first();
        $QualityName = $bidDetails->qualityName;

        $bidData = Bid::where(['id'  =>  $request->bid_id])->first();
        $sellerId = $bidData->seller_id;
        $userData = User::where('id', $sellerId)->where('userType' , 1)->first();

        $data = ['QualityName' => $QualityName, 'sno' => $request->bid_id, 'userData' => $userData];


        if ($request->counter_status == 1) {
            $response = MailController::html_email('mailcounteroffer', 'enquiry@sntcgroup.com', 'enquiry@sntcgroup.com', $data);
            // $response = MailController::html_email('mailcounteroffer' ,'rbajaj@sntcgroup.com','rbajaj@sntcgroup.com',$data); 
            // $response = MailController::html_email('mailcounteroffer' ,'vidula@sntcgroup.com','vidula@sntcgroup.com',$data); 
            // $response = MailController::html_email('mailcounteroffer' ,'leena@sntcgroup.com','leena@sntcgroup.com',$data); 
        } else {
            $response = MailController::html_email('mailcounterofferRejected', 'enquiry@sntcgroup.com', 'enquiry@sntcgroup.com', $data);
            // $response = MailController::html_email('mailcounterofferRejected' ,'rbajaj@sntcgroup.com','rbajaj@sntcgroup.com',$data); 
            // $response = MailController::html_email('mailcounterofferRejected' ,'vidula@sntcgroup.com','vidula@sntcgroup.com',$data); 
            // $response = MailController::html_email('mailcounterofferRejected' ,'leena@sntcgroup.com','leena@sntcgroup.com',$data); 
        }

        return response()->json(['status' => true, 'data' => $request->all()]);
    }
    public function updatePort(Request $request)
    {
        User::where('id', $request->id)->update(
            [
                'country' => $request->country,
                'import_port' => $request->port,
            ]
        );
        return response()->json(['status' => true]);
    }
    public function getHotDeals($userId)
    {
        $hotDealNotif = HotDealNotification::with(['getUSDDefaultMaster', 'getRiceQuality', 'HotDealAccept' => function ($query) use ($userId) {
            return $query->where('buyer_id', $userId)->get();
        }])->orderBy('id', 'desc')->take(50)->get();

        foreach ($hotDealNotif as $k => $v) {
            $v['isExpired'] = 'false';

            if ($v->status == 1) {
                if (Carbon::parse($v->validDate)->format('Y-m-d H:i') <= Carbon::now()->format('Y-m-d H:i')) {
                    $v['isExpired'] = 'true';
                    $v['isExpiredMessage'] = 'Expired';
                    // $v['isExpiredMessage'] = 'Deal Sold';
                }
            }

            if ($v->HotDealAccept != null) {
                if ($v->HotDealAccept->count() > 0) {
                    $v['isDealAcceptedMessage'] = "Thanks for showing interest in buying, Team SNTC will get in touch with you shortly.";
                }
            }
        }
        return response()->json(['status' => true, 'tfyh' => "here",  'data' => $hotDealNotif]);
    }
    public function acceptHotDealNotification(Request $request)
    {
        HotDealAccept::create(['hotdeal_id' => $request->bid_id, 'buyer_id' => $request->user_id, 'status' => 1]);
        $hotDealNotif = HotDealNotification::where('id', $request->bid_id)->first()->toArray();
        $response = MailController::html_email('mailNotification', 'enquiry@sntcgroup.com', 'enquiry@sntcgroup.com', $hotDealNotif);
        // $response = MailController::html_email('mailNotification','rbajaj@sntcgroup.com','rbajaj@sntcgroup.com'); 
        // $response = MailController::html_email('mailNotification','vidula@sntcgroup.com','vidula@sntcgroup.com'); 
        // $response = MailController::html_email('mailNotification','leena@sntcgroup.com','leena@sntcgroup.com'); 

        return response()->json(['status' => true, 'data' => $request->all()]);
    }
    public function getBagVendors()
    {
        $bagVendorCat = Vendorcategory::select('id', 'name')->where('status', 1)->get();
        return response()->json(['status' => true, 'data' => $bagVendorCat]);
    }
    public function paymentSuccess(Request $request)
    {
        $usdPlans = USDPlan::where('id', $request->planId)->get();
        if ($usdPrices->count() > 0) {
            $planId = $usdPlans[0]['id'];
            $validFor = $usdPlans[0]['valid_months'];
            $ValidMonthDate = Carbon::now()->addMonths($validFor)->format('Y-m-d');
        }
        User::where('id', $request->id)->update(['usd_role' => 7, 'is_usd_active' => '1', 'transaction_id' => $request->transaction_id, 'planId' => $planId, 'expired_on' => $ValidMonthDate]);
    }

    public function startTrialPerid($userId)
    {
        $expiredDate = Carbon::now()->addDays(30)->format('Y-m-d');
        $userHas = User::where('id', $userId)->where('userType' , 1)->first();
        $userHasUSDRole = $userHas['usd_role'];
        $setUserRole = 6;

        if ($userHasUSDRole != null && $userHasUSDRole != '') {
            $setUserRole = $userHasUSDRole;
        }
        User::where('id', $userId)->update(['transaction_id' => 'trial', 'expired_on' =>  $expiredDate, 'import_port' => 'Jebel Ali', 'usd_role' => $setUserRole, 'is_usd_active' => 1]);

        return response()->json(['status' => true, 'data' => ['expired_on' => $expiredDate, 'import_port' => 'Jebel Ali', 'usd_role' => $setUserRole, 'is_usd_active' => 1, 'transaction_id' => 'trial']]);
    }

    public function userNotification($userId)
    {
        $listNotifications = Notification::where('user_id', $userId)
            ->where('status', 0)
            ->where('is_cleared', 0)
            ->get();
        return response()->json(['status' => true, 'data' => $listNotifications->count()], 200);
    }

    public function clearNotifications($userId)
    {
        $cleared = Notification::where('user_id', $userId)
            ->where('is_cleared', 0)
            ->update(['is_cleared' => 1]);

        return response()->json([
            'status' => true,
            'cleared' => (int) $cleared,
            'data' => [],
        ], 200);
    }

    public function getRazorpayOrderId(Request $request)
    {
        $amount = $request->amount;

        $key_id = 'rzp_live_NY1vm28wpcuCKf';
        $secret = 'eTqutKKKWKjyq28vTsahFIcl';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"amount": ' . $amount . ',"currency": "INR","receipt": "receipt#1"}');
        curl_setopt($ch, CURLOPT_USERPWD, 'rzp_live_NY1vm28wpcuCKf' . ':' . 'eTqutKKKWKjyq28vTsahFIcl');

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        return response()->json(['status' => true, 'data' => $result], 200);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
    }

    public function deleteUser($userId)
    {
        // Keep api_token / mobile_api_token so open sessions get the blocked-account message.
        User::where('id', $userId)->update(['is_deactivated' => 1, 'user_token' => null]);
        return response()->json(['status' => true, 'data' => []], 200);
    }

    public function getBrandList()
    {
        $brands = Brand::orderBy('name')->with(['getAttachments'])->where('status' , 1)->get();
        return response()->json(['sttaus' => true, 'data' => $brands], 200);
    }
    /**
     * Web farming type options (TradeQueriesINR::$farmingTypeWeb).
     * GET api/web/get/farming-types
     */
    public function getFarmingTypesWeb()
    {
        return response()->json([
            'status' => true,
            'message' => 'Farming types fetched successfully.',
            'data' => TradeQueriesINR::$farmingTypeWeb,
        ]);
    }

    public function getRiceQualities($qualityTypeStatus)
    {
        $type = "non-basmati";
        if ($qualityTypeStatus == 1) {
            $type = 'basmati';
        }
        // $riceQuality = RiceName::select('name' , 'id')->orderBy('order', 'ASC')->where('status' , 1)->where('type' , $type)->pluck('id','name');
        $riceQuality = RiceName::select('name', 'id')->orderBy('order', 'ASC')->where('status', 1)->where('type', $type)->get();

        return response()->json(['status' => true, 'data' => $riceQuality]);
    }

    public function getRiceQualitiesName($getQualities)
    {
        $riceName = RiceName::where('id' ,$getQualities)->first();
        $riceNameType = $riceName->type;

        $riceQuality = RiceFormMilestone3::select(['id' , 'name', 'order'])->orderBy('order', 'ASC')->get();
        $riceform = RiceForm::select(['id' , 'form_name', 'order'])->orderBy('order', 'ASC')->where('status' , 1)->where('type' , $riceNameType)->get();

        return response()->json(['status' => true, 'data' => $riceQuality , 'riceform' => $riceform]);
    }

    /**
     * Secure web forms for a rice quality (rice_names.id). Forms from avg_length_maps only.
     */
    public function getWebRiceQualitiesName($riceId)
    {
        $riceId = (int) $riceId;
        if ($riceId <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid rice quality id.',
            ], 422);
        }

        $riceName = RiceName::find($riceId);
        if (! $riceName) {
            return response()->json([
                'status' => false,
                'message' => 'Rice quality not found.',
            ], 404);
        }

        $qualityType = strtolower(trim((string) $riceName->type));
        if (! in_array($qualityType, ['basmati', 'non-basmati'], true)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid rice quality type for this record.',
            ], 422);
        }

        $formIds = $this->resolveMappedRiceFormIds($riceId, (string) $riceName->type);

        if ($formIds === []) {
            return response()->json([
                'status' => true,
                'data' => [],
                'rice_name_id' => $riceId,
                'quality_type' => $qualityType,
            ]);
        }

        $forms = RiceFormMilestone3::select(['id', 'name', 'order'])
            ->whereIn('id', $formIds)
            ->where('status', 1)
            ->orderBy('order', 'ASC')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $forms,
            'rice_name_id' => $riceId,
            'quality_type' => $qualityType,
        ]);
    }

    public function getRiceWand($riceNameId)
    {
        $wand = WandModel::select(["id","RiceNameId","wandTypeId","value","order","status"])->where('RiceNameId', $riceNameId)->with(['getWandType' => function($q){
            return $q->select(["id","type","order","status"]);
        }])->orderBy('order', 'ASC')->get();
        return response()->json(['status' => true, 'data' => $wand]);
    }

    /**
     * Secure web wands for rice quality + form (avg_length_maps). Requires form_id query param.
     */
    public function getWebRiceWand(Request $request, $riceNameId)
    {
        $validator = Validator::make(
            array_merge($request->query(), ['rice_name_id' => $riceNameId]),
            [
                'rice_name_id' => ['required', 'integer', 'exists:rice_names,id'],
                'form_id' => ['required', 'integer', 'exists:rice_form_milestone3,id'],
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $riceNameId = (int) $riceNameId;
        $formId = (int) $request->query('form_id');

        $riceName = RiceName::find($riceNameId);
        if (! $riceName) {
            return response()->json([
                'status' => false,
                'message' => 'Rice quality not found.',
            ], 404);
        }

        $qualityType = strtolower(trim((string) $riceName->type));
        if (! in_array($qualityType, ['basmati', 'non-basmati'], true)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid rice quality type for this record.',
            ], 422);
        }

        $wandIds = $this->resolveMappedRiceWandIds($riceNameId, $formId, (string) $riceName->type);

        if ($wandIds === []) {
            return response()->json([
                'status' => true,
                'data' => [],
                'rice_name_id' => $riceNameId,
                'form_id' => $formId,
                'quality_type' => $qualityType,
            ]);
        }

        $wand = WandModel::select(['id', 'RiceNameId', 'wandTypeId', 'value', 'order', 'status'])
            ->where('RiceNameId', $riceNameId)
            ->whereIn('id', $wandIds)
            ->where('status', 1)
            ->with(['getWandType' => function ($q) {
                $q->select(['id', 'type', 'order', 'status']);
            }])
            ->orderBy('order', 'ASC')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $wand,
            'rice_name_id' => $riceNameId,
            'form_id' => $formId,
            'quality_type' => $qualityType,
        ]);
    }

    public function getSellerPackingINR()
    {
        $sellerPackingINR = SellerPackingINR::get();
        return response()->json(['status' => true, 'data' => $sellerPackingINR]);
    }


    public function FutureSubmitSellQuery(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge($this->rulesTradeQueryHierarchyIds(), [
                'user_id' => ['required', 'integer', 'exists:users,id'],
                'crop_year' => ['nullable', 'string', 'max:32'],
                'changePackingType' => ['required'],
                'quantity' => ['required'],
                'contactPerson' => ['nullable', 'string', 'max:255'],
                'contactMobile' => ['nullable', 'string', 'max:64'],
                'type' => ['nullable', 'string', 'max:32'],
                'extra_file' => ['nullable', 'file', 'max:15360'],
            ], $this->rulesFarmingWebId(), $this->rulesOptionalReportUpload()),
            [],
            array_merge($this->tradeQueryHierarchyAttributeNames(), $this->farmingWebAttributeNames())
        );
        if ($validator->fails()) {
            return $this->tradeQueryValidationFailedResponse($validator);
        }

        $data = [];

        $selectedQualityTypeInt = $request->selectedQualityTypeInt;
        $year = $request->crop_year?? '2023';
        $quality = $request->quality;
        $qualityForm = $request->qualityForm;
        $selectedGrade = $request->selectedGrade;
        $changePackingType = $request->changePackingType;
        $quantity = $request->quantity;
        $offerPrice = $request->input('offerPrice');
        $validDays = $this->normalizeValidDaysInputForRequest($request->input('validDays'));
        $contactperson = $request->contactPerson;
        $contactMobile = $request->contactMobile;
        $userId = $request->user_id;
        $farming = $this->resolveFarmingForQuerySave($request);
        $type = $request->type ?? 'app'; 

        if (isset($_FILES['extra_file'])) {
            $file_name      = $_FILES['extra_file']['name'];
            $file_size      = $_FILES['extra_file']['size'];
            $file_tmp       = $_FILES['extra_file']['tmp_name'];
            $file_type      = $_FILES['extra_file']['type'];
            if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            move_uploaded_file($file_tmp, "uploads/" . $file_name);
            $data['extra_file'] = $file_name;
        }

        if ($reportFile = $this->storeOptionalReportUpload($request)) {
            $data['report_file'] = $reportFile;
        }

        $data['farming'] = $farming ?? '';
        $data['year'] = $year;
        $data['quality_type'] = $selectedQualityTypeInt;
        $data['quality'] = $quality;
        $data['quality_form'] = $qualityForm;
        $data['grade'] = $selectedGrade;
        $data['packing'] = $changePackingType;
        $data['quantity'] = $quantity;
        $data['offerPrice'] = $offerPrice;
        $data['validDays'] = $validDays;
        $data['contactPerson'] = $contactperson;
        $data['contactMobile'] = $contactMobile;
        $data['created_by'] = $userId;
        $data['type'] = $type;


        $sellCreate = FutureSellQueriesINR::create($data);

        $mailPayload = $this->creatorDetailsForMail($userId);

        $mailTo = "enquiry@sntcgroup.com";
        $mailMessage = '';
        $subject = 'Future Sell with SNTC';
        $mailFrom = 'info@sntcgroup.com';
        $mailFromName = 'SNTC Team - India';

        $respose = Mail::send('mail.FutureSellQueryReceivedMilestone3', $mailPayload, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
            $message->to($mailTo, $mailMessage)->subject($subject);
            $message->from($mailFrom, $mailFromName);
        });
        return response()->json(['status' => true, 'data' => $sellCreate]);





    }


    public function FutureSubmitBuyQuery(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge($this->rulesTradeQueryHierarchyIds(), [
                'user_id' => ['required', 'integer', 'exists:users,id'],
                'crop_year' => ['nullable', 'string', 'max:32'],
                'changePackingType' => ['required'],
                'packing' => ['required'],
                'quantity' => ['required'],
                'contactPerson' => ['nullable', 'string', 'max:255'],
                'contactMobile' => ['nullable', 'string', 'max:64'],
                'type' => ['nullable', 'string', 'max:32'],
                'additionalinfo' => ['nullable', 'string'],
            ], $this->rulesFarmingWebId()),
            [],
            array_merge($this->tradeQueryHierarchyAttributeNames(), $this->farmingWebAttributeNames())
        );
        if ($validator->fails()) {
            return $this->tradeQueryValidationFailedResponse($validator);
        }

        $data = [];
        
        $farming = $this->resolveFarmingForQuerySave($request);
        $selectedQualityTypeInt = $request->selectedQualityTypeInt;
        $year = $request->crop_year?? '';
        $quality = $request->quality;
        $qualityForm = $request->qualityForm;
        $selectedGrade = $request->selectedGrade;

        $changePackingType = $request->changePackingType;
        $rate = $request->rate;
        $packing = $request->packing;
        $quantity = $request->quantity;

        $contactPerson = $request->contactPerson ?? '';
        $contactMobile = $request->contactMobile ?? '';
        $type = $request->type ?? 'app';

        $additionalinfo = $request->additionalinfo;
        $userId = $request->user_id;
       

        $data['farming'] = $farming ?? '';
        $data['quality_type'] = $selectedQualityTypeInt;


        $data['year'] = $year;
        $data['quality'] = $quality;
        $data['quality_form'] = $qualityForm;
        $data['grade'] = $selectedGrade;

        $data['packing_type'] = $changePackingType;
        $data['packing'] = $packing;
        $data['quantity'] = $quantity;

        $data['contactPerson'] = $contactPerson;
        $data['contactMobile'] = $contactMobile;
        $data['type'] = $type;

        $data['additional_info'] = $additionalinfo;
        $data['created_by'] = $userId;


        $buyerQuery = FutureBuyQueriesINR::create($data);

        $mailPayload = array_merge($this->creatorDetailsForMail($userId), [
            'contactPerson' => $contactPerson,
            'contactMobile' => $contactMobile,
        ]);

        $mailTo = "enquiry@sntcgroup.com";
        $mailMessage = '';
        $subject = 'Future Buy with SNTC ';
        $mailFrom = 'info@sntcgroup.com';
        $mailFromName = 'SNTC Team - India';

        $respose = Mail::send('mail.FutureBuyqueryReceivedMilestone3', $mailPayload, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
            $message->to($mailTo, $mailMessage)->subject($subject);
            $message->from($mailFrom, $mailFromName);
        });
        return response()->json(['status' => true, 'data' => $buyerQuery]);
    }


    public function SubmitSellQuery(Request $request)
    {
        $this->mergeValidDaysInputAliases($request);

        $validator = Validator::make(
            $request->all(),
            $this->rulesInrSellQuerySubmit(),
            [],
            array_merge($this->tradeQueryHierarchyAttributeNames(), $this->farmingWebAttributeNames(), $this->validDaysAttributeNames())
        );
        if ($validator->fails()) {
            return $this->tradeQueryValidationFailedResponse($validator);
        }

        $data = [];

        $selectedQualityTypeInt = $request->selectedQualityTypeInt;
        $quality = $request->quality;
        $qualityForm = $request->qualityForm;
        $selectedGrade = $request->selectedGrade;
        $changePackingType = $request->changePackingType;
        $quantity = $request->quantity;
        $offerPrice = $request->offerPrice;
        $validDays = $this->resolveValidDaysForQuerySave($request);
        $contactperson = $request->contactperson;
        $contactMobile = $request->contactMobile;
        $warehouselocation = $request->warehouselocation;
        $userId = $request->userId;
        $farming = $this->resolveFarmingForQuerySave($request);
        $riceSize = $request->riceSize;

        $type = $request->type ?? 'app';

        if (isset($_FILES['packageImageFile'])) {
            $file_name      = $_FILES['packageImageFile']['name'];
            $file_size      = $_FILES['packageImageFile']['size'];
            $file_tmp       = $_FILES['packageImageFile']['tmp_name'];
            $file_type      = $_FILES['packageImageFile']['type'];
if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            move_uploaded_file($file_tmp, "uploads/" . $file_name);
            $data['packing_file'] = $file_name;
        }

        if (isset($_FILES['uncookedFile'])) {
            $file_name      = $_FILES['uncookedFile']['name'];
            $file_size      = $_FILES['uncookedFile']['size'];
            $file_tmp       = $_FILES['uncookedFile']['tmp_name'];
            $file_type      = $_FILES['uncookedFile']['type'];
if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            move_uploaded_file($file_tmp, "uploads/" . $file_name);
            $data['uncooked_file'] = $file_name;
        }

        if (isset($_FILES['cookedImageFile'])) {
            $file_name      = $_FILES['cookedImageFile']['name'];
            $file_size      = $_FILES['cookedImageFile']['size'];
            $file_tmp       = $_FILES['cookedImageFile']['tmp_name'];
            $file_type      = $_FILES['cookedImageFile']['type'];
if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            move_uploaded_file($file_tmp, "uploads/" . $file_name);
            $data['cooked_file'] = $file_name;
        }

        if (isset($_FILES['extra_file'])) {
            $file_name      = $_FILES['extra_file']['name'];
            $file_size      = $_FILES['extra_file']['size'];
            $file_tmp       = $_FILES['extra_file']['tmp_name'];
            $file_type      = $_FILES['extra_file']['type'];
if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            move_uploaded_file($file_tmp, "uploads/" . $file_name);
            $data['extra_file'] = $file_name;
        }

        if ($reportFile = $this->storeOptionalReportUpload($request)) {
            $data['report_file'] = $reportFile;
        }

        $data['quality_type'] = $selectedQualityTypeInt;
        $data['quality'] = $quality;
        $data['qualityForm'] = $qualityForm;
        $data['grade'] = $selectedGrade;
        $data['packing'] = $changePackingType;
        $data['quantity'] = $quantity;
        $data['offerPrice'] = $offerPrice;
        $data['validDays'] = $validDays;
        $data['contactperson'] = $contactperson;
        $data['contactMobile'] = $contactMobile;
        $data['warehouselocation'] = $warehouselocation;
        $data['created_by'] = $userId;
        $data['farming'] = $farming ?? '';
        $data['type'] = $type;
        $data['riceSize'] = $riceSize;


        $sellCreate = SellQueriesINR::create($data);

        $mailPayload = $this->creatorDetailsForMail($userId);

        $mailTo = "enquiry@sntcgroup.com";
        $mailMessage = '';
        $subject = 'Sell with SNTC';
        $mailFrom = 'info@sntcgroup.com';
        $mailFromName = 'SNTC Team - India';

        $respose = Mail::send('mail.SellQueryReceivedMilestone3', $mailPayload, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
            $message->to($mailTo, $mailMessage)->subject($subject);
            $message->from($mailFrom, $mailFromName);
        });
        return response()->json(['status' => true, 'data' => $sellCreate]);
    }



    public function SubmitSellQueryWeb(Request $request)
    {
        $this->mergeValidDaysInputAliases($request);

        $validator = Validator::make(
            $request->all(),
            $this->rulesInrSellQuerySubmit(),
            [],
            array_merge($this->tradeQueryHierarchyAttributeNames(), $this->farmingWebAttributeNames(), $this->validDaysAttributeNames())
        );
        if ($validator->fails()) {
            return $this->tradeQueryValidationFailedResponse($validator);
        }

        $data = [];

        $selectedQualityTypeInt = $request->selectedQualityTypeInt;
        $quality = $request->quality;
        $qualityForm = $request->qualityForm;
        $selectedGrade = $request->selectedGrade;
        $changePackingType = $request->changePackingType;
        $quantity = $request->quantity;
        $offerPrice = $request->offerPrice;
        $validDays = $this->resolveValidDaysForQuerySave($request);
        $contactperson = $request->contactperson;
        $contactMobile = $request->contactMobile;
        $warehouselocation = $request->warehouselocation;
        $userId = $request->userId;
        $farming = $this->resolveFarmingForQuerySave($request);
        $riceSize = $request->riceSize;

        $type = $request->type ?? 'app';

        if (isset($_FILES['packageImageFile'])) {
            $file_name      = $_FILES['packageImageFile']['name'];
            $file_size      = $_FILES['packageImageFile']['size'];
            $file_tmp       = $_FILES['packageImageFile']['tmp_name'];
            $file_type      = $_FILES['packageImageFile']['type'];
if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            move_uploaded_file($file_tmp, "uploads/" . $file_name);
            $data['packing_file'] = $file_name;
        }

        if (isset($_FILES['uncookedFile'])) {
            $file_name      = $_FILES['uncookedFile']['name'];
            $file_size      = $_FILES['uncookedFile']['size'];
            $file_tmp       = $_FILES['uncookedFile']['tmp_name'];
            $file_type      = $_FILES['uncookedFile']['type'];
if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            move_uploaded_file($file_tmp, "uploads/" . $file_name);
            $data['uncooked_file'] = $file_name;
        }

        if (isset($_FILES['cookedImageFile'])) {
            $file_name      = $_FILES['cookedImageFile']['name'];
            $file_size      = $_FILES['cookedImageFile']['size'];
            $file_tmp       = $_FILES['cookedImageFile']['tmp_name'];
            $file_type      = $_FILES['cookedImageFile']['type'];
if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            move_uploaded_file($file_tmp, "uploads/" . $file_name);
            $data['cooked_file'] = $file_name;
        }

        if (isset($_FILES['extra_file'])) {
            $file_name      = $_FILES['extra_file']['name'];
            $file_size      = $_FILES['extra_file']['size'];
            $file_tmp       = $_FILES['extra_file']['tmp_name'];
            $file_type      = $_FILES['extra_file']['type'];
if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            move_uploaded_file($file_tmp, "uploads/" . $file_name);
            $data['extra_file'] = $file_name;
        }

        if ($reportFile = $this->storeOptionalReportUpload($request)) {
            $data['report_file'] = $reportFile;
        }

        $data['quality_type'] = $selectedQualityTypeInt;
        $data['quality'] = $quality;
        $data['qualityForm'] = $qualityForm;
        $data['grade'] = $selectedGrade;
        $data['packing'] = $changePackingType;
        $data['quantity'] = $quantity;
        $data['offerPrice'] = $offerPrice;
        $data['validDays'] = $validDays;
        $data['contactperson'] = $contactperson;
        $data['contactMobile'] = $contactMobile;
        $data['warehouselocation'] = $warehouselocation;
        $data['created_by'] = $userId;
        $data['farming'] = $farming ?? '';
        $data['type'] = $type;
        $data['riceSize'] = $riceSize;


        $sellCreate = SellQueriesINR::create($data);

        $mailPayload = $this->creatorDetailsForMail($userId);

        $mailTo = "enquiry@sntcgroup.com";
        $mailMessage = '';
        $subject = 'Sell with SNTC ';
        $mailFrom = 'info@sntcgroup.com';
        $mailFromName = 'SNTC Team - India';

        $respose = Mail::send('mail.SellQueryReceivedMilestone3', $mailPayload, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
            $message->to($mailTo, $mailMessage)->subject($subject);
            $message->from($mailFrom, $mailFromName);
        });
        return response()->json(['status' => true, 'data' => $sellCreate]);
    }



    public function getTrade($userId)
    {
        $now = $this->expirePastValidDayTrades();
        $nowStr = $now->format('Y-m-d H:i:s');
        $eagerLoads = $this->mobileTradeListEagerLoads($userId);

        $openTrades = TradeQueriesINR::query()
            ->whereIn('status', [1, 4, 6])
            ->where('validDays', '>', $nowStr)
            ->with($eagerLoads)
            ->orderBy('id', 'DESC')
            ->withCount('TradeLikeAll')
            ->get();

        $soldTrades = TradeQueriesINR::query()
            ->where('status', 3)
            ->with($eagerLoads)
            ->orderBy('id', 'DESC')
            ->limit(15)
            ->withCount('TradeLikeAll')
            ->get();

        $allTrade = $this->orderMobileTradesListing($openTrades->concat($soldTrades));
        $trade = $allTrade->groupBy('tradeType');

        $tradeStatus = TradeCurrentStatus::first();

        return response()->json(['status' => true, 'data' => $trade, 'allTrade' => $allTrade, 'currentStatus' => $tradeStatus['currentStatus'], 'statusMessage' => $tradeStatus['message']]);
    }

    /**
     * Web trade list (All types). No trade_type filter.
     * Order by login role: Seller/Supplier → sell, buy, sold; Buyer/others → buy, sell, sold.
     * Response includes trade_list_meta.counts and sell/buy_starts_at_page for pagination UX.
     */
    public function getWebTrades(Request $request, $userId)
    {
        $this->expirePastValidDayTrades();

        $interestUserId = $this->resolveWebTradeInterestUserId($request, $userId);

        $allTrade = TradeQueriesINR::query()
            ->tap(fn ($query) => $this->applyWebTradeListScope($query, $request, false, null))
            ->with([
                'TradeInterest' => function ($query) use ($interestUserId) {
                    $query->where('userId', $interestUserId);
                },
                'RiceNameData',
                'TradeLikeAll' => function ($query) use ($interestUserId) {
                    $query->where('userId', $interestUserId);
                },
                'RiceFormMilestone3',
                'RiceFormData',
                'riceGrade' => function ($query) {
                    $query->with('getWandType');
                },
                'RicePackingBuyer',
                'RicePackingSeller',
            ])
            ->orderBy('id', 'DESC')
            ->withCount('TradeLikeAll')
            ->get();

        $allTrade = $this->orderWebTradesAllTypesListing($allTrade, $interestUserId);
        $allTrade = $this->formatTradeCollectionValidDays($allTrade);
        $allTrade = $this->stripTradeCollectionRelationTimestamps($allTrade);

        $paginated = $this->paginateOrderedTrades($allTrade, $request);
        $trade = $paginated['items'];

        $tradeStatus = TradeCurrentStatus::first();
        $tradeListMeta = $this->buildWebTradeListMeta($allTrade, $request, false, null, $interestUserId);

        return response()->json([
            'status' => true,
            'data' => $trade,
            'pagination' => $paginated['pagination'],
            'total' => $paginated['pagination']['total'],
            'last_page' => $paginated['pagination']['last_page'],
            'trade_list_meta' => $tradeListMeta,
            'currentStatus' => $tradeStatus['currentStatus'],
            'statusMessage' => $tradeStatus['message'],
            'user_interests_applied' => UserInterestService::getActiveInterestTuplesForUser($interestUserId) !== [],
        ]);
    }


    public function getPersonalQuery($userId)
    {
        $BuyQueriesINR = $this->applyPackingLogic(
            BuyQueriesINR::where('created_by', $userId)
                ->with([
                    'RiceFormMilestone3:id,name',
                    'RiceQualityRiceNames:id,name',
                    'riceGrade:id,value,wandTypeId',
                    'RicePacking:id,packing,description'
                ])
                ->get()
        );

        $SellQueriesINR = $this->formatSellQueryCollectionValidDays(
            $this->applyPackingLogic(
                SellQueriesINR::where('created_by', $userId)
                    ->selectRaw("
                        sell_query_milestone3.*,
                        contactperson AS contactPerson
                    ")
                    ->with([
                        'RiceFormMilestone3:id,name',
                        'RiceQualityRiceNames:id,name',
                        'riceGrade:id,value,wandTypeId',
                        'RicePacking:id,packing,description'
                    ])
                    ->get()
            )
        );

        $FutureBuyQueriesINR = $this->applyPackingLogic(
            FutureBuyQueriesINR::where('created_by', $userId)
                ->with([
                    'RiceQualityRiceNames:id,name',
                    'riceGrade:id,value',
                    'RicePacking:id,packing,description',
                    'UserDetail:id,name',
                    'RiceFormMilestone3:id,name'
                ])
                ->get()
        );

        $FutureSellQueriesINR = $this->formatSellQueryCollectionValidDays(
            $this->applyPackingLogic(
                FutureSellQueriesINR::where('created_by', $userId)
                    ->selectRaw('future_sell_query_milestone3.*, contactPerson AS contactPerson')
                    ->with([
                        'RiceQualityRiceNames:id,name',
                        'riceGrade:id,value',
                        'RicePacking:id,packing,description',
                        'UserDetail:id,name',
                        'RiceFormMilestone3:id,name',
                    ])
                    ->get()
            )
        );

        return response()->json([
            'status' => true,
            'data' => [
                'buy' => $this->attachFarmingRelationToQueries($BuyQueriesINR),
                'sell' => $this->attachFarmingRelationToQueries($SellQueriesINR),
                'futureBuy' => $this->attachFarmingRelationToQueries($FutureBuyQueriesINR),
                'futureSell' => $this->attachFarmingRelationToQueries($FutureSellQueriesINR),
            ],
        ]);
    }

    private function applyPackingLogic($collection)
    {
        return $collection->transform(function ($item) {

            if ($item->packing_type == 0 && $item->packing == 0) {
                $item->setRelation('RicePacking', new Buyerpackinginr([
                    'id' => 0,
                    'packing' => '50 kg PP',
                    'description' => null
                ]));
            }

            if ($item->packing_type == 0 && $item->packing == 1) {
                $item->setRelation('RicePacking', new Buyerpackinginr([
                    'id' => 1,
                    'packing' => '55 kg PP',
                    'description' => null
                ]));
            }

            return $item;
        });
    }

    /**
     * Attach farming type details for personal query responses (farming column stores type id).
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $collection
     * @return \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection
     */
    private function attachFarmingRelationToQueries($collection)
    {
        return $collection->map(function ($item) {
            $farmingId = TradeQueriesINR::resolveFarmingId(
                $item->getAttributes()['farming'] ?? $item->farming ?? null
            );

            $item->setAttribute(
                'farming_relation',
                $farmingId !== null ? [
                    'id' => $farmingId,
                    'name' => TradeQueriesINR::resolveFarmingName($farmingId),
                ] : null
            );

            return $item;
        });
    }

    public function getPersonalTrade($userId)
    {
        $BuyQueriesINR = BuyQueriesINR::where('created_by' , $userId)->pluck('id')->toArray();
        $SellQueriesINR = SellQueriesINR::where('created_by' , $userId)->pluck('id')->toArray();
        $FutureBuyQueriesINR = FutureBuyQueriesINR::where('created_by' , $userId)->pluck('id')->toArray();
        $FutureSellQueriesINR = FutureSellQueriesINR::where('created_by' , $userId)->pluck('id')->toArray();
        
        // tradeType: 1 = buy, 2 = sell, 3 = future buy, 4 = future sell (tradeFor is App/Web)
        $BuyQuery = $this->personalLinkedTradesQuery($BuyQueriesINR, 1)->get();
        $SellQuery = $this->personalLinkedTradesQuery($SellQueriesINR, 2)->get();
        $FutureBuyQuery = $this->personalLinkedTradesQuery($FutureBuyQueriesINR, 3)->get();
        $FutureSellQuery = $this->personalLinkedTradesQuery($FutureSellQueriesINR, 4)->get();

        return response()->json(['status' => true, 'data' => ['BuyQuery' => $BuyQuery , 'SellQuery' => $SellQuery , 'FutureBuyQuery' => $FutureBuyQuery , 'FutureSellQuery' => $FutureSellQuery]]);
    }

    public function getTradeCounts(Request $request)
    {
        $now = $this->expirePastValidDayTrades();

        $baseTradeQuery = TradeQueriesINR::where('status', 1)
            ->where('validDays', '>', $now->format('Y-m-d H:i:s'))
            ->where(function ($query) use ($request) {
                $this->applyTradeCountFilters($query, $request);
            });

        $BuyQuery = (clone $baseTradeQuery)->where('tradeType', 1)->count();
        $SellQuery = (clone $baseTradeQuery)->where('tradeType', 2)->count();
        $FutureBuyQuery = (clone $baseTradeQuery)->where('tradeType', 3)->count();
        $FutureSellQuery = (clone $baseTradeQuery)->where('tradeType', 4)->count();

        return response()->json(['status' => true, 'data' => ['BuyQuery' => $BuyQuery , 'SellQuery' => $SellQuery , 'FutureBuyQuery' => $FutureBuyQuery , 'FutureSellQuery' => $FutureSellQuery]]);
    }

    /**
     * Optional filters for get/all/trades/count (GET query or small POST JSON body).
     */
    private function applyTradeCountFilters($query, Request $request): void
    {
        $filters = [
            'farming_type' => 'farmingType',
            'quality_type' => 'quality_type',
            'quality' => 'quality',
            'quality_form' => 'qualityFormLinkWithLivePrice',
            'rice_size' => 'riceSize',
        ];

        foreach ($filters as $requestKey => $column) {
            $value = $this->resolveTradeFilterRequestValue($request, [$requestKey]);
            if ($value !== null && $value !== '') {
                $query->where($column, $value);
            }
        }
    }

    public function getPersonalQueryCount($userId)
    {
        $availableQueries =
            BuyQueriesINR::where('created_by', $userId)->count()
            + SellQueriesINR::where('created_by', $userId)->count()
            + FutureBuyQueriesINR::where('created_by', $userId)->count()
            + FutureSellQueriesINR::where('created_by', $userId)->count();

        return response()->json([
            'status' => true,
            'data' => [
                'availableQueries' => $availableQueries,
                'trades' => $this->countPersonalMovedToTradeQueries($userId),
                'soldCount' => $this->countPersonalLinkedTrades($userId, 3),
            ],
        ]);
    }

    public function filterTrade(Request $request , $userId)
    {
        $now = $this->expirePastValidDayTrades();
        $nowStr = $now->format('Y-m-d H:i:s');
        $eagerLoads = $this->mobileTradeListEagerLoads($userId);

        $applyFilters = function ($query) use ($request) {
            if ($request->has('trade_type')) {
                $query->where('tradeType', $request->trade_type);
            }
            if ($request->has('farming_type')) {
                $query->where('farmingType', $request->farming_type);
            }
            if ($request->has('quality_type')) {
                $query->where('quality_type', $request->quality_type);
            }
            if ($request->has('quality')) {
                $query->where('quality', $request->quality);
            }
            if ($request->has('quality_form')) {
                $query->where('qualityFormLinkWithLivePrice', $request->quality_form);
            }
            if ($request->has('rice_size')) {
                $query->where('riceSize', $request->rice_size);
            }
        };

        $openTrades = TradeQueriesINR::query()
            ->whereIn('status', [1, 4, 6])
            ->where('validDays', '>', $nowStr)
            ->where($applyFilters)
            ->with($eagerLoads)
            ->orderBy('id', 'DESC')
            ->withCount('TradeLikeAll')
            ->get();

        $soldTrades = TradeQueriesINR::query()
            ->where('status', 3)
            ->where($applyFilters)
            ->with($eagerLoads)
            ->orderBy('id', 'DESC')
            ->limit(15)
            ->withCount('TradeLikeAll')
            ->get();

        $allTrade = $this->orderMobileTradesListing($openTrades->concat($soldTrades));
        $allTrade = $this->formatTradeCollectionValidDays($allTrade);
        $trade = $allTrade->groupBy('tradeType');

        $tradeStatus = TradeCurrentStatus::first();

        return response()->json(['status' => true, 'data' => $trade, 'allTrade' => $allTrade, 'currentStatus' => $tradeStatus['currentStatus'], 'statusMessage' => $tradeStatus['message']]);
    }

    /**
     * Web trade list with optional filters.
     * All tab: omit trade_type (or send 0 / "all") — role-aware buy/sell order then 15 sold;
     * use trade_list_meta.sell_starts_at_page / buy_starts_at_page when paginating.
     * Buy/Sell tab: send trade_type 1–4 — active trades only for that type.
     */
    public function webFilterTrade(Request $request , $userId)
    {
        $this->expirePastValidDayTrades();

        $interestUserId = $this->resolveWebTradeInterestUserId($request, $userId);
        $hasTradeTypeFilter = $this->hasWebTradeFilterTradeType($request);
        $appliedTradeType = $this->resolveAppliedWebTradeFilterTradeType($request);

        $allTrade = TradeQueriesINR::query()
            ->tap(fn ($query) => $this->applyWebTradeListScope($query, $request, $hasTradeTypeFilter, $appliedTradeType))
            ->with([
                'TradeInterest' => function ($query) use ($interestUserId) {
                    $query->where('userId', $interestUserId);
                },
                'RiceNameData',
                'TradeLikeAll' => function ($query) use ($interestUserId) {
                    $query->where('userId', $interestUserId);
                },
                'RiceFormMilestone3',
                'RiceFormData',
                'riceGrade' => function ($query) {
                    $query->with('getWandType');
                },
                'RicePackingBuyer',
                'RicePackingSeller'
            ])
            ->orderBy('id', 'DESC')
            ->withCount('TradeLikeAll')
            ->get();

        $allTrade = $hasTradeTypeFilter
            ? $this->orderWebTradesForUserListing($allTrade, $interestUserId)
            : $this->orderWebTradesAllTypesListing($allTrade, $interestUserId);
        $allTrade = $this->formatTradeCollectionValidDays($allTrade);
        $allTrade = $this->stripTradeCollectionRelationTimestamps($allTrade);

        $paginated = $this->paginateOrderedTrades($allTrade, $request);
        $trade = $paginated['items'];

        $tradeStatus = TradeCurrentStatus::first();
        $tradeListMeta = $this->buildWebTradeListMeta($allTrade, $request, $hasTradeTypeFilter, $appliedTradeType, $interestUserId);

        return response()->json([
            'status' => true,
            'data' => $trade,
            // 'allTrade' => $trade,
            'pagination' => $paginated['pagination'],
            'total' => $paginated['pagination']['total'],
            'last_page' => $paginated['pagination']['last_page'],
            'trade_list_meta' => $tradeListMeta,
            'currentStatus' => $tradeStatus['currentStatus'],
            'statusMessage' => $tradeStatus['message'],
            'user_interests_applied' => UserInterestService::getActiveInterestTuplesForUser($interestUserId) !== [],
        ]);
    }

    /**
     * Slice an already-ordered trade collection for API pagination (order is unchanged).
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $trades
     * @return array{items: \Illuminate\Support\Collection, pagination: array<string, int|null>}
     */
    private function paginateOrderedTrades($trades, Request $request): array
    {
        $collection = $trades instanceof \Illuminate\Support\Collection ? $trades : collect($trades);
        $total = $collection->count();
        $perPage = $this->resolveTradeFilterPerPage($request);
        $requestedPage = $this->resolveTradeFilterPage($request);
        $lastPage = $total === 0 ? 0 : (int) ceil($total / $perPage);

        if ($total === 0) {
            $page = max(1, $requestedPage);
            $items = collect();
            $from = null;
            $to = null;
        } elseif ($lastPage === 0 || $requestedPage > $lastPage) {
            $page = $requestedPage;
            $items = collect();
            $from = null;
            $to = null;
        } else {
            $page = $requestedPage;
            $offset = ($page - 1) * $perPage;
            $items = $collection->slice($offset, $perPage)->values();
            $from = $offset + 1;
            $to = min($offset + $perPage, $total);
        }

        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
                'from' => $from,
                'to' => $to,
            ],
        ];
    }

    /**
     * Pagination page from POST body (JSON/form) or query string.
     */
    private function resolveTradeFilterPage(Request $request): int
    {
        $value = $this->resolveTradeFilterPaginationValue($request, ['page']);

        return max(1, (int) ($value ?? 1));
    }

    /**
     * Pagination size from query string or POST body (JSON/form).
     */
    private function resolveTradeFilterPerPage(Request $request): int
    {
        $value = $this->resolveTradeFilterPaginationValue($request, ['per_page', 'perPage', 'limit']);

        return max(1, min(200, (int) ($value ?? 50)));
    }

    /**
     * Pagination params: query string first (URL), then POST/JSON body.
     *
     * @param  array<int, string>  $keys
     */
    private function resolveTradeFilterPaginationValue(Request $request, array $keys)
    {
        foreach ($keys as $key) {
            if ($request->query->has($key)) {
                $value = $request->query->get($key);
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }
        }

        foreach ($keys as $key) {
            if ($request->request->has($key)) {
                $value = $request->request->get($key);
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }

            if ($request->json() && $request->json()->has($key)) {
                $value = $request->json()->get($key);
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }
        }

        foreach ($keys as $key) {
            $value = $request->input($key);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * Read a request value from POST payload first, then query string.
     *
     * @param  array<int, string>  $keys
     */
    private function resolveTradeFilterRequestValue(Request $request, array $keys)
    {
        foreach ($keys as $key) {
            if ($request->request->has($key)) {
                $value = $request->request->get($key);
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }

            if ($request->json() && $request->json()->has($key)) {
                $value = $request->json()->get($key);
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }
        }

        foreach ($keys as $key) {
            if ($request->query->has($key)) {
                $value = $request->query->get($key);
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }
        }

        foreach ($keys as $key) {
            $value = $request->input($key);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * True when filter payload includes a specific trade_type (Buy/Sell/Future).
     * Values treated as "All" (no filter): null, empty, 0, "0", "all" (case-insensitive).
     */
    private function hasWebTradeFilterTradeType(Request $request): bool
    {
        return $this->resolveAppliedWebTradeFilterTradeType($request) !== null;
    }

    /**
     * Resolved trade_type filter (1–4) or null when listing all types.
     */
    private function resolveAppliedWebTradeFilterTradeType(Request $request): ?int
    {
        $value = $this->resolveTradeFilterRequestValue($request, ['trade_type']);
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value) && strtolower(trim($value)) === 'all') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $tradeType = (int) $value;

        return $tradeType > 0 ? $tradeType : null;
    }

    /**
     * Breakdown for web trade list responses (All vs filtered, buy/sell counts, sell/buy page hints).
     * Active counts come from DB (status IN 1,4,6) per tradeType — not from the in-memory list.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $orderedTrades
     * @return array<string, mixed>
     */
    private function buildWebTradeListMeta($orderedTrades, Request $request, bool $hasTradeTypeFilter, ?int $appliedTradeType, ?int $userId = null): array
    {
        $collection = $orderedTrades instanceof \Illuminate\Support\Collection ? $orderedTrades : collect($orderedTrades);
        $perPage = $this->resolveTradeFilterPerPage($request);
        $activeCounts = $this->countWebActiveTradesForListing($request, $hasTradeTypeFilter, $appliedTradeType);
        $listOrder = $this->resolveWebTradeListSideOrder($userId);

        $soldInList = 0;
        $preferredActive = 0;
        $preferredBuyActive = 0;
        $preferredSellActive = 0;
        foreach ($collection as $trade) {
            if ((int) $trade->status === 3) {
                $soldInList++;
                continue;
            }
            if (! empty($trade->matches_user_interest) || (int) ($trade->interest_match_score ?? 0) > 0) {
                $preferredActive++;
                if ($this->isWebBuyTradeType((int) $trade->tradeType)) {
                    $preferredBuyActive++;
                } elseif ($this->isWebSellTradeType((int) $trade->tradeType)) {
                    $preferredSellActive++;
                }
            }
        }

        $sellStartsAtPage = null;
        $buyStartsAtPage = null;
        $totalActiveSell = $activeCounts['sell_active'] + $activeCounts['future_sell_active'];
        $totalActiveBuy = $activeCounts['buy_active'] + $activeCounts['future_buy_active'];

        if (! $hasTradeTypeFilter) {
            if ($totalActiveSell > 0) {
                $position = 0;
                foreach ($collection as $trade) {
                    $position++;
                    if (
                        $this->isWebSellTradeType((int) $trade->tradeType)
                        && $this->isWebActiveTradeStatus((int) $trade->status)
                    ) {
                        $sellStartsAtPage = (int) ceil($position / $perPage);
                        break;
                    }
                }
            }

            if ($totalActiveBuy > 0) {
                $position = 0;
                foreach ($collection as $trade) {
                    $position++;
                    if (
                        $this->isWebBuyTradeType((int) $trade->tradeType)
                        && $this->isWebActiveTradeStatus((int) $trade->status)
                    ) {
                        $buyStartsAtPage = (int) ceil($position / $perPage);
                        break;
                    }
                }
            }
        }

        $interestCount = ($userId !== null && $userId > 0)
            ? count(UserInterestService::getActiveInterestTuplesForUser($userId))
            : 0;

        return [
            'trade_type_filter_applied' => $hasTradeTypeFilter,
            'applied_trade_type' => $appliedTradeType,
            'list_side_order' => $listOrder,
            'user_interest_rows' => $interestCount,
            'counts' => array_merge($activeCounts, [
                'sold_in_list' => $soldInList,
                'preferred_active' => $preferredActive,
                'preferred_buy_active' => $preferredBuyActive,
                'preferred_sell_active' => $preferredSellActive,
            ]),
            'list_total' => array_sum($activeCounts) + $soldInList,
            'sell_starts_at_page' => $sellStartsAtPage,
            'buy_starts_at_page' => $buyStartsAtPage,
        ];
    }

    /**
     * Mark trades past validDays as expired (status = 2).
     * Returns "now" used for the comparison so list queries stay consistent.
     */
    private function expirePastValidDayTrades(): Carbon
    {
        $now = Carbon::now(config('app.timezone', 'Asia/Kolkata'));
        $nowStr = $now->format('Y-m-d H:i:s');

        TradeQueriesINR::whereIn('status', [1, 4, 5, 6, 11, 12])
            ->whereNotNull('validDays')
            ->where('validDays', '<=', $nowStr)
            ->update(['status' => 2]);

        return $now;
    }

    /**
     * Eager loads shared by mobile get/filter trade list endpoints.
     *
     * @return array<string, mixed>
     */
    private function mobileTradeListEagerLoads($userId): array
    {
        return [
            'TradeInterest' => function ($query) use ($userId) {
                $query->where('userId', $userId);
            },
            'RiceNameData',
            'TradeLikeAll' => function ($query) use ($userId) {
                $query->select(['id', 'tradeId'])->where('userId', $userId);
            },
            'RiceFormMilestone3',
            'riceGrade' => function ($query) {
                $query->with('getWandType');
            },
            'RicePackingBuyer',
            'RicePackingSeller',
        ];
    }

    /**
     * Mobile trade list order:
     * 1) pending (1)
     * 2) in-process (4)
     * 3) active (6)
     * 4) latest 15 sold (3)
     * Within each status bucket: id DESC.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $trades
     * @return \Illuminate\Support\Collection
     */
    private function orderMobileTradesListing($trades)
    {
        $collection = $trades instanceof \Illuminate\Support\Collection ? $trades : collect($trades);
        if ($collection->isEmpty()) {
            return $collection->values();
        }

        $bucket = function (int $status) use ($collection) {
            return $collection
                ->filter(fn ($trade) => (int) $trade->status === $status)
                ->sortByDesc(fn ($trade) => (int) $trade->id)
                ->values();
        };

        $sold = $bucket(3)->take(15);

        return $bucket(1)
            ->concat($bucket(4))
            ->concat($bucket(6))
            ->concat($sold)
            ->values();
    }

    /**
     * Status / tradeType scope for web trade listing queries.
     */
    private function applyWebTradeListScope($query, Request $request, bool $hasTradeTypeFilter, ?int $appliedTradeType): void
    {
        $nowStr = Carbon::now(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d H:i:s');

        if ($hasTradeTypeFilter) {
            $query->whereIn('status', [1, 4, 6])
                ->where('validDays', '>', $nowStr)
                ->where('tradeType', $appliedTradeType);
        } else {
            // All tab: active trades + sold (for latest-15 bucket); exclude expired, de-active, hold, close.
            $query->whereIn('status', [1, 4, 6, 3])
                ->where(function ($q) use ($nowStr) {
                    $q->where('status', 3)
                        ->orWhere('validDays', '>', $nowStr);
                })
                ->whereIn('tradeType', [1, 2, 3, 4]);
        }

        $this->applyWebTradeListOptionalFilters($query, $request);
    }

    /**
     * Optional web trade filters (farming, quality, state, packing, etc.).
     */
    private function applyWebTradeListOptionalFilters($query, Request $request): void
    {
        if ($request->has('farming_type')) {
            $query->where('farmingType', $request->farming_type);
        }

        if ($request->has('quality_type')) {
            $query->where('quality_type', $request->quality_type);
        }

        if ($request->has('quality')) {
            $query->where('quality', $request->quality);
        }

        if ($request->has('quality_form')) {
            if ($request->has('state')) {
                $query->where('qualityFormLinkWithLivePrice', $request->quality_form);
            } else {
                $query->where('qualityForm', $request->quality_form);
            }
        }

        if ($request->has('rice_size')) {
            $query->where('riceSize', $request->rice_size);
        }

        if ($request->has('state')) {
            $query->where('stateLinkWithLivePrice', $request->state);
        }

        if ($request->has('packing')) {
            $query->where('packingStreamType', $request->packing);
        }
    }

    /**
     * Active trade counts from DB: status IN (1,4,6), one count per tradeType (1–4).
     *
     * @return array{buy_active: int, sell_active: int, future_buy_active: int, future_sell_active: int}
     */
    private function countWebActiveTradesForListing(Request $request, bool $hasTradeTypeFilter, ?int $appliedTradeType): array
    {
        $empty = [
            'buy_active' => 0,
            'sell_active' => 0,
            'future_buy_active' => 0,
            'future_sell_active' => 0,
        ];

        $base = TradeQueriesINR::query()
            ->whereIn('status', [1, 4, 6])
            ->where('validDays', '>', Carbon::now(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d H:i:s'));
        $this->applyWebTradeListOptionalFilters($base, $request);

        if ($hasTradeTypeFilter) {
            $total = (clone $base)->where('tradeType', $appliedTradeType)->count();
            $key = [1 => 'buy_active', 2 => 'sell_active', 3 => 'future_buy_active', 4 => 'future_sell_active'][$appliedTradeType] ?? null;
            if ($key === null) {
                return $empty;
            }
            $empty[$key] = $total;

            return $empty;
        }

        return [
            'buy_active' => (clone $base)->where('tradeType', 1)->count(),
            'sell_active' => (clone $base)->where('tradeType', 2)->count(),
            'future_buy_active' => (clone $base)->where('tradeType', 3)->count(),
            'future_sell_active' => (clone $base)->where('tradeType', 4)->count(),
        ];
    }

    /**
     * Prefer authenticated portal token user for Preferred/order; fall back to route userId.
     */
    private function resolveWebTradeInterestUserId(Request $request, $routeUserId): int
    {
        // Prefer the route userId (whose list this is). Fall back to auth user.
        // Route id is the source of truth for Preferred interests on web/get/trades/{userId}.
        $routeId = (int) $routeUserId;
        if ($routeId > 0) {
            return $routeId;
        }

        $authUser = $request->user();
        if ($authUser && (int) ($authUser->id ?? 0) > 0) {
            return (int) $authUser->id;
        }

        return 0;
    }

    /**
     * Web user's primary category from business profile (e.g. Manufacturer); maps to category.id / trade_category_map.category_id.
     */
    private function resolveWebUserCategoryId(int $userId): ?int
    {
        $selected = WebBusinessDetails::where('user_id', $userId)->value('selected_category');
        if ($selected === null || $selected === '') {
            return null;
        }
        $id = (int) $selected;

        return $id > 0 ? $id : null;
    }

    /**
     * Web trade list order (Buy/Sell filtered tabs):
     * 1) non-sold trades matching user Preferred products (interests)
     * 2) non-sold trades matching admin web categories (excluding bucket 1)
     * 3) remaining non-sold trades by id DESC
     * 4) latest 15 sold trades by id DESC
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $trades
     * @return \Illuminate\Support\Collection
     */
    private function orderWebTradesForUserListing($trades, int $userId)
    {
        $collection = $trades instanceof \Illuminate\Support\Collection ? $trades : collect($trades);
        if ($collection->isEmpty()) {
            return $collection->values();
        }

        $userCategoryId = $this->resolveWebUserCategoryId($userId);
        $categoryTradeIds = [];
        if ($userCategoryId !== null) {
            $categoryTradeIds = TradeCategoryMap::query()
                ->where('category_id', $userCategoryId)
                ->where('status', 1)
                ->pluck('trade_id')
                ->flip()
                ->all();
        }

        $interestTuples = UserInterestService::getActiveInterestTuplesForUser($userId);

        $sold = $collection->filter(fn ($trade) => (int) $trade->status === 3);
        $nonSold = $collection->filter(fn ($trade) => (int) $trade->status !== 3);

        $placedIds = [];

        // 1) Preferred interests first (exact grade score before name+form).
        $bucket1 = [];
        foreach ($nonSold as $trade) {
            $score = UserInterestService::scoreTradeAgainstInterests($trade, $interestTuples);
            if ($score <= 0) {
                continue;
            }
            $placedIds[(int) $trade->id] = true;
            $trade->setAttribute('matches_user_category', isset($categoryTradeIds[$trade->id]));
            $trade->setAttribute('matches_user_interest', true);
            $trade->setAttribute('interest_match_score', $score);
            $bucket1[] = $trade;
        }
        usort($bucket1, function ($a, $b) {
            $scoreCmp = (int) $b->interest_match_score <=> (int) $a->interest_match_score;
            if ($scoreCmp !== 0) {
                return $scoreCmp;
            }

            return (int) $b->id <=> (int) $a->id;
        });

        // 2) Category matches next (excluding already-preferred).
        $bucket2 = [];
        foreach ($nonSold as $trade) {
            if (isset($placedIds[(int) $trade->id])) {
                continue;
            }
            if (! isset($categoryTradeIds[$trade->id])) {
                continue;
            }
            $placedIds[(int) $trade->id] = true;
            $trade->setAttribute('matches_user_category', true);
            $trade->setAttribute('matches_user_interest', false);
            $trade->setAttribute('interest_match_score', 0);
            $bucket2[] = $trade;
        }
        usort($bucket2, fn ($a, $b) => (int) $b->id <=> (int) $a->id);

        // 3) Remaining active trades.
        $bucket3 = [];
        foreach ($nonSold as $trade) {
            if (isset($placedIds[(int) $trade->id])) {
                continue;
            }
            $trade->setAttribute('matches_user_category', false);
            $trade->setAttribute('matches_user_interest', false);
            $trade->setAttribute('interest_match_score', 0);
            $bucket3[] = $trade;
        }
        usort($bucket3, fn ($a, $b) => (int) $b->id <=> (int) $a->id);

        $bucket4 = $sold
            ->sortByDesc(fn ($trade) => (int) $trade->id)
            ->values()
            ->take(15)
            ->map(function ($trade) use ($categoryTradeIds, $interestTuples) {
                $score = UserInterestService::scoreTradeAgainstInterests($trade, $interestTuples);
                $trade->setAttribute('matches_user_category', isset($categoryTradeIds[$trade->id]));
                $trade->setAttribute('matches_user_interest', $score > 0);
                $trade->setAttribute('interest_match_score', $score);

                return $trade;
            })
            ->all();

        return collect(array_merge($bucket1, $bucket2, $bucket3, $bucket4))->values();
    }

    /**
     * All trade types (no trade_type filter), Preferred first, then role-aware side order, then latest 15 sold.
     *
     * With Preferred interests:
     * 1) Preferred primary side (buyer→buy / seller→sell)
     * 2) Preferred other side
     * 3) Non-preferred primary side
     * 4) Non-preferred other side
     * 5) Sold (latest 15)
     *
     * Without Preferred: primary → other → sold (Case1 only).
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $trades
     * @return \Illuminate\Support\Collection
     */
    private function orderWebTradesAllTypesListing($trades, ?int $userId = null)
    {
        $collection = $trades instanceof \Illuminate\Support\Collection ? $trades : collect($trades);
        if ($collection->isEmpty()) {
            return $collection->values();
        }

        $buyActive = $collection->filter(
            fn ($trade) => $this->isWebActiveTradeStatus((int) $trade->status)
                && $this->isWebBuyTradeType((int) $trade->tradeType)
        );
        $sellActive = $collection->filter(
            fn ($trade) => $this->isWebActiveTradeStatus((int) $trade->status)
                && $this->isWebSellTradeType((int) $trade->tradeType)
        );
        $soldMixed = $collection
            ->filter(fn ($trade) => (int) $trade->status === 3)
            ->sortByDesc(fn ($trade) => (int) $trade->id)
            ->values()
            ->take(15);

        $interestTuples = ($userId !== null && $userId > 0)
            ? UserInterestService::getActiveInterestTuplesForUser($userId)
            : [];

        $sellFirst = $this->resolveWebTradeListSideOrder($userId) === 'sell_first';

        if ($interestTuples === []) {
            $buySorted = $this->annotateAndSortWebTradesByStatusThenId($buyActive, []);
            $sellSorted = $this->annotateAndSortWebTradesByStatusThenId($sellActive, []);

            if ($sellFirst) {
                return $sellSorted->concat($buySorted)->concat($soldMixed)->values();
            }

            return $buySorted->concat($sellSorted)->concat($soldMixed)->values();
        }

        [$buyPreferred, $buyOther] = $this->splitWebTradesByInterest($buyActive, $interestTuples);
        [$sellPreferred, $sellOther] = $this->splitWebTradesByInterest($sellActive, $interestTuples);

        $buyPreferredSorted = $this->sortWebTradesByInterestScoreThenStatusId($buyPreferred);
        $sellPreferredSorted = $this->sortWebTradesByInterestScoreThenStatusId($sellPreferred);
        $buyOtherSorted = $this->sortWebTradesByStatusThenId($buyOther);
        $sellOtherSorted = $this->sortWebTradesByStatusThenId($sellOther);

        if ($sellFirst) {
            // Seller/Supplier: preferred sell → preferred buy → other sell → other buy → sold
            return $sellPreferredSorted
                ->concat($buyPreferredSorted)
                ->concat($sellOtherSorted)
                ->concat($buyOtherSorted)
                ->concat($soldMixed)
                ->values();
        }

        // Buyer/others: preferred buy → preferred sell → other buy → other sell → sold
        return $buyPreferredSorted
            ->concat($sellPreferredSorted)
            ->concat($buyOtherSorted)
            ->concat($sellOtherSorted)
            ->concat($soldMixed)
            ->values();
    }

    /**
     * Annotate interest flags (score 0) and sort by status then id.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $trades
     * @param  array<int, array{rice_name_id:int, form_id:int, grade:int|null}>  $interestTuples
     * @return \Illuminate\Support\Collection
     */
    private function annotateAndSortWebTradesByStatusThenId($trades, array $interestTuples)
    {
        $items = ($trades instanceof \Illuminate\Support\Collection ? $trades : collect($trades))->values();
        foreach ($items as $trade) {
            $score = $interestTuples === []
                ? 0
                : UserInterestService::scoreTradeAgainstInterests($trade, $interestTuples);
            $trade->setAttribute('matches_user_interest', $score > 0);
            $trade->setAttribute('interest_match_score', $score);
        }

        return $this->sortWebTradesByStatusThenId($items);
    }

    /**
     * Split active trades into preferred (score > 0) and non-preferred.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $trades
     * @param  array<int, array{rice_name_id:int, form_id:int, grade:int|null}>  $interestTuples
     * @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection}
     */
    private function splitWebTradesByInterest($trades, array $interestTuples): array
    {
        $preferred = collect();
        $other = collect();

        foreach (($trades instanceof \Illuminate\Support\Collection ? $trades : collect($trades)) as $trade) {
            $score = UserInterestService::scoreTradeAgainstInterests($trade, $interestTuples);
            $trade->setAttribute('matches_user_interest', $score > 0);
            $trade->setAttribute('interest_match_score', $score);

            if ($score > 0) {
                $preferred->push($trade);
            } else {
                $other->push($trade);
            }
        }

        return [$preferred, $other];
    }

    /**
     * Preferred bucket: higher interest score first, then status priority, then newer id.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $trades
     * @return \Illuminate\Support\Collection
     */
    private function sortWebTradesByInterestScoreThenStatusId($trades)
    {
        $statusOrder = [6 => 0, 4 => 1, 1 => 2, 12 => 3, 11 => 4];
        $items = ($trades instanceof \Illuminate\Support\Collection ? $trades : collect($trades))->values()->all();

        usort($items, function ($a, $b) use ($statusOrder) {
            $scoreCmp = (int) ($b->interest_match_score ?? 0) <=> (int) ($a->interest_match_score ?? 0);
            if ($scoreCmp !== 0) {
                return $scoreCmp;
            }

            $statusA = $statusOrder[(int) $a->status] ?? 99;
            $statusB = $statusOrder[(int) $b->status] ?? 99;
            if ($statusA !== $statusB) {
                return $statusA <=> $statusB;
            }

            return (int) $b->id <=> (int) $a->id;
        });

        return collect($items);
    }

    /**
     * Preferred active-trade side order for the All-trades web list.
     * Seller (4) / Supplier (6) → sell_first; Buyer (5) and everyone else → buy_first.
     *
     * @return 'sell_first'|'buy_first'
     */
    private function resolveWebTradeListSideOrder(?int $userId): string
    {
        if ($userId === null || $userId <= 0) {
            return 'buy_first';
        }

        $user = User::query()
            ->select(['id', 'role'])
            ->with(['role_rel:id,role_name'])
            ->find($userId);

        if (! $user) {
            return 'buy_first';
        }

        $roleId = (int) ($user->role ?? 0);
        $roleName = strtolower(trim((string) optional($user->role_rel)->role_name));

        // Known IDs: Seller=4, Supplier=6. Also match by role_name for safety.
        if (in_array($roleId, [4, 6], true) || in_array($roleName, ['seller', 'supplier'], true)) {
            return 'sell_first';
        }

        return 'buy_first';
    }

    /**
     * Web trade list order:
     * 1) non-sold buy (tradeType 1,3) by status then id
     * 2) non-sold sell (tradeType 2,4) by status then id
     * 3) sold buy
     * 4) sold sell
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $trades
     * @return \Illuminate\Support\Collection
     */
    private function orderWebTradesActiveBeforeSold($trades, ?int $userCategoryId = null)
    {
        $collection = $trades instanceof \Illuminate\Support\Collection ? $trades : collect($trades);

        $buyActive = $collection->filter(fn ($trade) => (int) $trade->status !== 3 && $this->isWebBuyTradeType((int) $trade->tradeType));
        $sellActive = $collection->filter(fn ($trade) => (int) $trade->status !== 3 && $this->isWebSellTradeType((int) $trade->tradeType));
        $buySold = $collection->filter(fn ($trade) => (int) $trade->status === 3 && $this->isWebBuyTradeType((int) $trade->tradeType));
        $sellSold = $collection->filter(fn ($trade) => (int) $trade->status === 3 && $this->isWebSellTradeType((int) $trade->tradeType));

        $buckets = [
            $this->sortWebTradesByStatusThenId($buyActive),
            $this->sortWebTradesByStatusThenId($sellActive),
            $this->sortWebTradesByIdDesc($buySold),
            $this->sortWebTradesByIdDesc($sellSold),
        ];

        if ($userCategoryId !== null) {
            $buckets = array_map(
                fn ($bucket) => $this->orderWebTradesWithUserCategoryFirst($bucket, $userCategoryId),
                $buckets
            );
        }

        $ordered = collect();
        foreach ($buckets as $bucket) {
            $ordered = $ordered->concat($bucket->values());
        }

        return $ordered->values();
    }

    private function isWebBuyTradeType(int $tradeType): bool
    {
        return in_array($tradeType, [1, 3], true);
    }

    private function isWebSellTradeType(int $tradeType): bool
    {
        return in_array($tradeType, [2, 4], true);
    }

    private function isWebActiveTradeStatus(int $status): bool
    {
        return in_array($status, [1, 4, 6], true);
    }

    /**
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $trades
     * @return \Illuminate\Support\Collection
     */
    private function sortWebTradesByStatusThenId($trades)
    {
        $statusOrder = [6 => 0, 4 => 1, 1 => 2, 12 => 3, 11 => 4];
        $items = ($trades instanceof \Illuminate\Support\Collection ? $trades : collect($trades))->values()->all();

        usort($items, function ($a, $b) use ($statusOrder) {
            $statusA = $statusOrder[(int) $a->status] ?? 99;
            $statusB = $statusOrder[(int) $b->status] ?? 99;

            if ($statusA !== $statusB) {
                return $statusA <=> $statusB;
            }

            return (int) $b->id <=> (int) $a->id;
        });

        return collect($items);
    }

    /**
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $trades
     * @return \Illuminate\Support\Collection
     */
    private function sortWebTradesByIdDesc($trades)
    {
        $items = ($trades instanceof \Illuminate\Support\Collection ? $trades : collect($trades))->values()->all();

        usort($items, fn ($a, $b) => (int) $b->id <=> (int) $a->id);

        return collect($items);
    }

    /**
     * Put trades that include the user's category in trade_category_map first; keep existing order (status priority, then id) within each group.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $trades
     * @return \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection
     */
    private function orderWebTradesWithUserCategoryFirst($trades, ?int $userCategoryId)
    {
        if ($userCategoryId === null || $trades->isEmpty()) {
            return $trades;
        }

        $matchIds = TradeCategoryMap::query()
            ->where('category_id', $userCategoryId)
            ->where('status', 1)
            ->pluck('trade_id')
            ->flip()
            ->all();

        if (empty($matchIds)) {
            return $trades;
        }

        $first = [];
        $rest = [];
        foreach ($trades as $trade) {
            if (isset($matchIds[$trade->id])) {
                $first[] = $trade;
            } else {
                $rest[] = $trade;
            }
        }

        return collect(array_merge($first, $rest));
    }

    /**
     * Format validDays for web trade APIs (IST, e.g. 12-04-2026, 7:00 PM).
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $trades
     * @return \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection
     */
    private function formatTradeCollectionValidDays($trades)
    {
        return $trades->map(function ($trade) {
            if (! empty($trade->validDays)) {
                try {
                    $formatted = Carbon::parse($trade->validDays)
                        ->timezone('Asia/Kolkata')
                        ->format('d-m-Y, g:i A');
                    $trade->setAttribute('validDays', $formatted);
                } catch (\Throwable $e) {
                    // leave original if unparsable
                }
            }

            $trade->setAttribute(
                'farmingName',
                TradeQueriesINR::resolveFarmingName($trade->farmingType) ?? ''
            );

            $trade->setAttribute(
                'is_new',
                ((int) ($trade->getAttributes()['is_new'] ?? $trade->is_new ?? 0)) === 1 ? 'yes' : 'no'
            );

            $trade->setAttribute(
                'valid_datetime_for_is_new',
                $trade->getAttributes()['valid_datetime_for_is_new'] ?? $trade->valid_datetime_for_is_new ?? null
            );

            return $trade;
        });
    }

    /**
     * Remove created_at / updated_at from eager-loaded relations on trade API responses.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $trades
     * @return \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection
     */
    private function stripTradeCollectionRelationTimestamps($trades)
    {
        return $trades->map(function ($trade) {
            $this->stripLoadedRelationTimestamps($trade);

            return $trade;
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    private function stripLoadedRelationTimestamps($model): void
    {
        if (! $model instanceof \Illuminate\Database\Eloquent\Model) {
            return;
        }

        foreach ($model->getRelations() as $relation) {
            if ($relation instanceof \Illuminate\Support\Collection) {
                $relation->each(function ($item) {
                    if ($item instanceof \Illuminate\Database\Eloquent\Model) {
                        $item->makeHidden(['created_at', 'updated_at']);
                        $this->stripLoadedRelationTimestamps($item);
                    }
                });

                continue;
            }

            if ($relation instanceof \Illuminate\Database\Eloquent\Model) {
                $relation->makeHidden(['created_at', 'updated_at']);
                $this->stripLoadedRelationTimestamps($relation);
            }
        }
    }

    /**
     * Account holder (created_by user) for enquiry emails: name, email, phone.
     *
     * @return array{creator_name: string, creator_email: string, creator_phone: string}
     */
    private function creatorDetailsForMail($userId): array
    {
        $empty = [
            'creator_name' => '—',
            'creator_email' => '—',
            'creator_phone' => '—',
        ];
        if ($userId === null || $userId === '' || (int) $userId <= 0) {
            return $empty;
        }
        $u = User::query()->where('id', (int) $userId)->first(['name', 'email', 'phone', 'mobile']);
        if (! $u) {
            return $empty;
        }
        $phone = $u->phone ?: $u->mobile;

        return [
            'creator_name' => $u->name ?: '—',
            'creator_email' => $u->email ?: '—',
            'creator_phone' => $phone ?: '—',
        ];
    }

    /**
     * Public rice sourcing list for guests (no token). Only trades that are still open for sourcing:
     * not sold (3), not expired (2), not de-active/closed/hold (5, 11, 12), and validDays still in the future.
     * Optional filters match web/get/trades/filter: trade_type, farming_type, quality_type, quality, quality_form, rice_size, state, packing.
     */
    public function getGuestRiceSourcingTrades(Request $request)
    {
        $now = $this->expirePastValidDayTrades();

        $allTrade = TradeQueriesINR::query()
            ->whereIn('status', [1, 4, 6])
            ->where('validDays', '>', $now->format('Y-m-d H:i:s'))
            ->where(function ($query) use ($request) {
                if ($request->has('trade_type')) {
                    $query->where('tradeType', $request->trade_type);
                } else {
                    $query->whereIn('tradeType', [1, 2, 3, 4]);
                }

                if ($request->has('farming_type')) {
                    $query->where('farmingType', $request->farming_type);
                }

                if ($request->has('quality_type')) {
                    $query->where('quality_type', $request->quality_type);
                }

                if ($request->has('quality')) {
                    $query->where('quality', $request->quality);
                }

                if ($request->has('quality_form')) {
                    if ($request->has('state')) {
                        $query->where('qualityFormLinkWithLivePrice', $request->quality_form);
                    } else {
                        $query->where('qualityForm', $request->quality_form);
                    }
                }

                if ($request->has('rice_size')) {
                    $query->where('riceSize', $request->rice_size);
                }

                if ($request->has('state')) {
                    $query->where('stateLinkWithLivePrice', $request->state);
                }

                if ($request->has('packing')) {
                    $query->where('packingStreamType', $request->packing);
                }
            })
            ->orderByRaw('FIELD(status,6,4,1)')
            ->orderBy('id', 'DESC')
            ->limit(150)
            ->with([
                'RiceNameData',
                'RiceFormMilestone3',
                'RiceFormData',
                'riceGrade' => function ($query) {
                    $query->with('getWandType');
                },
                'RicePackingBuyer',
                'RicePackingSeller',
            ])
            ->withCount('TradeLikeAll')
            ->get();

        $allTrade = $this->formatTradeCollectionValidDays($allTrade);

        $tradeStatus = TradeCurrentStatus::first();

        return response()->json([
            'status' => true,
            'data' => $allTrade,
            'currentStatus' => $tradeStatus['currentStatus'] ?? null,
            'statusMessage' => $tradeStatus['message'] ?? null,
        ]);
    }

    /**
     * Public list of posted jobs that are still open for applications (last_date_apply on or after today).
     */
    public function getPublicPostedJobs()
    {
        $today = Carbon::today();

        $rows = PostedJob::query()
            ->where('status', 1)
            ->whereDate('last_date_apply', '>=', $today)
            ->orderBy('last_date_apply', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        $typeLabels = PostedJob::employmentTypeOptions();

        $data = $rows->map(function (PostedJob $job) use ($typeLabels) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'description' => $job->description,
                'job_role' => $job->job_role,
                'location' => $job->location,
                'type' => $job->employment_type,
                'type_label' => $typeLabels[$job->employment_type] ?? null,
                'last_date_apply' => $job->last_date_apply ? $job->last_date_apply->format('Y-m-d') : null,
                'number_of_positions' => (int) $job->number_of_positions,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Posted jobs retrieved successfully.',
            'data' => $data,
        ]);
    }

    /**
     * Public: submit a job application (multipart supported for optional CV).
     * Expects application_id = posted_jobs.id for the job being applied to.
     */
    public function saveJobApplication(Request $request)
    {
        $validated = $request->validate([
            'application_id' => 'required|integer|exists:posted_jobs,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'mobile' => 'required|string|max:64',
            'experience' => 'nullable|string|max:10000',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:15360',
        ]);

        $job = PostedJob::query()
            ->where('id', $validated['application_id'])
            ->where('status', 1)
            ->whereDate('last_date_apply', '>=', Carbon::today())
            ->first();

        if (! $job) {
            return response()->json([
                'status' => false,
                'message' => 'This job is not open for applications or does not exist.',
            ], 422);
        }

        $cvRelativePath = null;
        if ($request->hasFile('cv')) {
            $file = $request->file('cv');
            $ext = strtolower($file->getClientOriginalExtension());
            $safe = 'cv_' . time() . '_' . Str::random(10) . '.' . $ext;
            $relativeDir = 'uploads/job_applications';
            $destDir = public_path($relativeDir);
            if (! is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            $file->move($destDir, $safe);
            $cvRelativePath = $relativeDir . '/' . $safe;
        }

        $row = JobApplication::create([
            'posted_job_id' => (int) $validated['application_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'experience' => $validated['experience'] ?? null,
            'cv_file' => $cvRelativePath,
            'status' => 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Application submitted successfully.',
            'data' => [
                'id' => $row->id,
                'application_id' => $row->posted_job_id,
                'name' => $row->name,
                'email' => $row->email,
                'mobile' => $row->mobile,
                'experience' => $row->experience,
                'cv_file' => $row->cv_file,
                'status' => (int) $row->status,
            ],
        ], 201);
    }

    public function getTradeDetail($tradeId)
    {
        $trade = TradeQueriesINR::where('id', $tradeId)->with(['RiceFormMilestone3', 'RiceQualityMaster', 'riceGrade' => function ($query) {
            return $query->with('getWandType')->get();
        }, 'RicePacking'])->first();

        return response()->json(['status' => true, 'data' => $trade]);
    }

    public function getBuyerPackingINR()
    {
        $buyerPacking = Buyerpackinginr::get();
        return response()->json(['status' => true, 'data' => $buyerPacking]);
    }
    
    public function SubmitBuyQuery(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge($this->rulesTradeQueryHierarchyIds(requireGrade: false), [
                'user_id' => ['required', 'integer', 'exists:users,id'],
                'changePackingType' => ['required'],
                'packing' => ['required'],
                'quantity' => ['required'],
                'additionalinfo' => ['nullable', 'string'],
                'contactPerson' => ['nullable', 'string', 'max:255'],
                'contactMobile' => ['nullable', 'string', 'max:64'],
                'type' => ['nullable', 'string', 'max:32'],
            ], $this->rulesFarmingWebId()),
            [],
            array_merge($this->tradeQueryHierarchyAttributeNames(), $this->farmingWebAttributeNames())
        );
        if ($validator->fails()) {
            return $this->tradeQueryValidationFailedResponse($validator);
        }

        $data = [];

        $selectedQualityTypeInt = $request->selectedQualityTypeInt;
        $quality = $request->quality;
        $qualityForm = $request->qualityForm;
        $changePackingType = $request->changePackingType;
        $packing = $request->packing;
        $quantity = $request->quantity;
        $additionalinfo = $request->additionalinfo;
        $userId = $request->user_id;
        $farming = $this->resolveFarmingForQuerySave($request);
        $contactPerson = $request->contactPerson ?? '';
        $contactMobile = $request->contactMobile ?? '';
        $type = $request->type ?? 'app';

        $data['farming'] = $farming ?? '';
        $data['contactPerson'] = $contactPerson;
        $data['contactMobile'] = $contactMobile;
        $data['type'] = $type;

        $data['quality_type'] = $selectedQualityTypeInt;
        $data['quality'] = $quality;
        $data['quality_form'] = $qualityForm;
        $data['grade'] = $this->coalesceEmptyTradeQuerySelection($request->input('selectedGrade')) ?? '';
        $data['packing_type'] = $changePackingType;
        $data['packing'] = $packing;
        $data['quantity'] = $quantity;
        $data['additional_info'] = $additionalinfo;
        $data['contactPerson'] = $contactPerson;
        $data['contactMobile'] = $contactMobile;
        $data['created_by'] = $userId;


        $buyerQuery = BuyQueriesINR::create($data);
        $viewData = array_merge($this->creatorDetailsForMail($userId), [
            'contactPerson' => $contactPerson,
            'contactMobile' => $contactMobile,
        ]);

        $mailTo = "enquiry@sntcgroup.com";
        // $mailTo = "sandy.singh51480@gmail.com";
        $mailMessage = '';
        if( $type == 'web' ){
            $subject = 'Buy with SNTC ' ;
        }else{
            $subject = 'Buy with SNTC' ;
        }
        $mailFrom = 'info@sntcgroup.com';
        $mailFromName = 'SNTC Team - India';

        $respose = Mail::send('mail.BuyqueryReceivedMilestone3', $viewData, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
            $message->to($mailTo, $mailMessage)->subject($subject);
            $message->from($mailFrom, $mailFromName);
        });

        return response()->json(['status' => true, 'data' => $buyerQuery]);
    }

    public function likeTrade(Request $request)
    {
        $tradeId = $request->tradeId;
        $userId = $request->userId;


        $tradeLike = TradeLike::create(['tradeId' => $tradeId, 'userId' => $userId]);
        if ($tradeLike) {
            return response()->json(['status' => true, 'data' => []], 200);
        } else {
            return response()->json(['status' => false, 'data' => []], 500);
        }
    }

    public function tradeintrested(Request $request)
    {
        $tradeId = $request->tradeId;
        $userId = $request->userId;

        // $userDetails = User::where(['id' => $userId,'userType' => 1])->first();
        $userDetails = User::where(['id' => $userId])->first();

        // $mailTo = "sandy.singh51480@gmail.com";
        $mailTo = "enquiry@sntcgroup.com";
        $mailMessage = '';
        $subject = 'Notification of trade interested SNTC';
        $mailFrom = 'info@sntcgroup.com';
        $mailFromName = 'SNTC Team - India';

        $data = ['username' => $userDetails->name, 'email' => $userDetails->email, 'mobile' => $userDetails->mobile, 'tradeId' => $tradeId, 'companyName' => $userDetails->companyname];

        $respose = Mail::send('mail.TradeRequest', $data, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
            $message->to($mailTo, $mailMessage)->subject($subject);
            $message->from($mailFrom, $mailFromName);
        });

        $tradeLike = TradeIntrested::create(['tradeId' => $tradeId, 'userId' => $userId]);
        if ($tradeLike) {
            return response()->json(['status' => true, 'data' => []], 200);
        } else {
            return response()->json(['status' => false, 'data' => []], 500);
        }
    }

    public function webTradeintrested(Request $request)
    {
        $tradeId = $request->tradeId;
        $userId = $request->userId;

        // $userDetails = User::where(['id' => $userId,'userType' => 1])->first();
        $userDetails = User::where(['id' => $userId])->first();

        // $mailTo = "sandy.singh51480@gmail.com";
        $mailTo = "enquiry@sntcgroup.com";
        $mailMessage = '';
        $subject = 'Notification of trade interested SNTC -';
        $mailFrom = 'info@sntcgroup.com';
        $mailFromName = 'SNTC Team - India';

        $data = ['username' => $userDetails->name, 'email' => $userDetails->email, 'mobile' => $userDetails->mobile, 'tradeId' => $tradeId, 'companyName' => $userDetails->companyname];

        $respose = Mail::send('mail.TradeRequest', $data, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
            $message->to($mailTo, $mailMessage)->subject($subject);
            $message->from($mailFrom, $mailFromName);
        });

        $tradeLike = TradeIntrested::create(['tradeId' => $tradeId, 'userId' => $userId]);
        if ($tradeLike) {
            return response()->json(['status' => true, 'data' => []], 200);
        } else {
            return response()->json(['status' => false, 'data' => []], 500);
        }
    }

    public function NewsRunner()
    {
        $news = NewsRunner::where('status', 1)->orderBy('id', 'desc')->get()->groupBy('type')->map(function ($query) {
            return $query->take(1);
        });
        return response()->json(['status' => true, 'data' => $news], 200);
    }

    public function getWebNewsRunner()
    {
        $news = WebNewsRunner::where('status', 1)->orderBy('id', 'desc')->get()->groupBy('newsType')->map(function ($query) {
            return $query->take(1);
        });
        return response()->json(['status' => true, 'data' => $news], 200);
    }

    public function getPackingByTradeType($tradeType)
    {
        if ($tradeType == 2) {
            $packingType  = Buyerpackinginr::get();
        } else {
            $packingType  = SellerPackingINR::get();
        }
        return response()->json(['status' => true, 'data' => $packingType], 200);
    }

    public function getBagPacking()
    {
        $packingType = PackingType::select(['id' , 'name'])->get();
        return response()->json(['status' => true, 'data' => $packingType], 200);
    }

    public function getTestimonial()
    {
        $testimonial = Testimonial::get();
        return response()->json(['status' => true, 'data' => $testimonial], 200);
    }
    public function getTestimonialVideos()
    {
        $testimonial = TestimonialVideo::get();
        return response()->json(['status' => true,'basePath' => 'uploads/testimonial/video','data' => $testimonial ], 200);
    }
    public function contactUs(Request $request)
    {
        $data = $request->all();

        $validation = \Validator::make($data , [
            'fullName' => 'required' ,
            'email' => 'required' ,
            'phone' => 'required' ,
            'message' => 'required'
        ]);

        if( $validation->fails() )  {
            return response()->json(['status' => false, 'data' => [] , 'message' => $validation->errors()], 500);
        }
        try {
            $response = MailController::sendContactUsMail('info@sntcgroup.com', 'no@replay.in', 'SNTC GROUP', 'You got a new contact query from web','You got a new contact query from web', $data);
            return response()->json(['status' => true, 'data' => [] , 'message' => 'mail sent successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'data' => [] , 'message' => 'something went wrong'], 500);
        }
    }

    public function listGrade()
    {
        $grade = WandTypeModel::select('type' , 'id')->where(['status' => 1])->orderBy('order')->get();
        return response()->json(['status' => true , 'message' => 'Grade get successfully' , 'data' => $grade]);
    }


    public function getRiceForms()
    {
        $riceForms = RiceForm::select('id' , 'form_name')->where('status' , 1)->orderBy('order')->get();
        return response()->json(['status' => true , 'message' => 'Forms get successfully' , 'data' => $riceForms]);
    }



    public function getMyTrades(Request $request)
    {
        $trade = TradeQueriesINR::where('queryId' , $request->userId)->get();
        $trade = $this->formatTradeCollectionValidDays($trade);
        return response()->json(['status' => true , 'message' => 'Trade get successfully' , 'data' => $trade]);
    }

    public function getCategoryByRole($roleId)
    {   
        // if( $roleId == 11 ){
        //     $categoryRole = WebVendorCategory::select('id' , 'name')->where('status' , 1)->get();
        // }else{
            $categoryRole = CategoryRoleMap::select(['id', 'role', 'category'])
                ->whereHas('category_rel', function ($q) {
                    $q->where('status', 1);
                })
                ->with([
                    'role_rel:id,role_name',
                    'category_rel' => function ($q){
                        $q->select('id' , 'category')->where('status' , 1);
                    }
                ])
                ->where('role', $roleId)
                ->orderBy(
                    Category::select('order')
                        ->whereColumn('id', 'category_role_map.category')
                )->where('status' , 1)
                ->get();
        // }
        
        return response()->json(['status' => true , 'message' => 'category role get successfully' , 'data' => $categoryRole]);
    }

    public function saveBrandAvailability(Request $request)
    {
        $processDate = [];
        if($request->has('brand_id')){
            $branchId = $request['brand_id'];
            if( $request->has('availability') ){
                $availability = $request['availability'];

                foreach( $availability as $k => $v ){
                    if( isset($v['state_id']) )  {
                        $stateId = $v['state_id'];
                        if( isset($v['city']) ){
                            foreach($v['city'] as $key => $val){
                                $processDate[] = ['brand_id' => $branchId , 'state_id' => $stateId , 'city_id' => $val];
                            }
                        }
                    }
                    
                    
                }
            }
            BrandAvailability::insert($processDate);
            return response()->json(['status' => true , 'message' => 'Brand Availability added successfully' , 'data' => []] , 200);
        }
        return response()->json(['status' => false , 'message' => 'Required fields are missing' , 'data' => []] , 402);
    }

    public function getBrandAvailability($brandId)
    {
        $brandId = (int) $brandId;
        if ($brandId < 1) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid brand id',
                'data' => [],
            ], 422);
        }

        $brandAvail = BrandAvailability::groupedForBrand($brandId);

        return response()->json([
            'status' => true,
            'message' => 'Brand Availability get successfully',
            'data' => $brandAvail,
        ], 200);
    }

    /**
     * Open API for guest users.
     * Returns the same response format as getPrices(),
     * using today records or latest fallback through existing getPrices logic.
     *
     * Query params:
     * - year (optional)
     */
    public function getLivePricesToday(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'year' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $cropYear = $request->get('year');
        $cacheKey = 'api:live_prices_today:'.md5(json_encode(['year' => $cropYear]));

        $payload = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($cropYear) {
            if ($cropYear !== null && $cropYear !== '') {
                request()->merge(['year' => $cropYear]);
            }

            return $this->getPrices('PUNJAB-HARYANA', 'basmati')->getData(true);
        });

        return response()->json($payload);
    }

    public function adminIsViewedByAdmin()
    {
        $hasUnattendedUser = User::where(['user_from' => 'web', 'is_viewed_by_admin' => 0])->count();
        return response()->json([ 'status' => true , 'message' => 'count get successfully' , 'count' => $hasUnattendedUser ] , 200);
    }

    /**
     * Decode a base64 value from a URL path segment: spaces vs "+", URL-safe alphabet, missing padding.
     */
    private function decodeEncodedRouteSegment(string $encoded): string
    {
        $encoded = trim(rawurldecode($encoded));
        $encoded = str_replace(' ', '+', $encoded);

        $decoded = base64_decode($encoded, true);
        if ($decoded !== false) {
            return trim($decoded);
        }

        $padLen = strlen($encoded) % 4;
        if ($padLen !== 0) {
            $padded = $encoded . str_repeat('=', 4 - $padLen);
            $decoded = base64_decode($padded, true);
            if ($decoded !== false) {
                return trim($decoded);
            }
        }

        $urlSafe = strtr($encoded, '-_', '+/');
        if ($urlSafe !== $encoded) {
            $decoded = base64_decode($urlSafe, true);
            if ($decoded !== false) {
                return trim($decoded);
            }
            $padLen = strlen($urlSafe) % 4;
            if ($padLen !== 0) {
                $padded = $urlSafe . str_repeat('=', 4 - $padLen);
                $decoded = base64_decode($padded, true);
                if ($decoded !== false) {
                    return trim($decoded);
                }
            }
        }

        return $encoded;
    }

    /**
     * Resolve a rice row from decoded chart/price input: numeric id, then exact name, then name as digit string.
     */
    private function resolveRiceNameFromEncodedInput(string $riceInput): ?RiceName
    {
        $riceInput = trim($riceInput);
        $riceInput = str_replace('_', ' ', $riceInput);
        $compact = preg_replace('/\s+/', '', $riceInput);

        if ($compact !== '' && ctype_digit($compact)) {
            $byId = RiceName::where('id', (int) $compact)->first();
            if ($byId) {
                return $byId;
            }
        }

        $byName = RiceName::where('name', $riceInput)->first();
        if ($byName) {
            return $byName;
        }

        if ($compact !== '' && ctype_digit($compact)) {
            return RiceName::where('name', $compact)->first();
        }

        return null;
    }

    /**
     * When multiple live_prices rows share the same IST calendar day, keep the last added row (highest id).
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $prices
     * @return \Illuminate\Support\Collection
     */
    private function collapseLivePricesToLatestPerDay($prices)
    {
        return $prices
            ->filter(fn ($row) => $row->created_at !== null)
            ->groupBy(fn ($row) => $row->created_at->copy()->timezone('Asia/Kolkata')->format('Y-m-d'))
            ->map(fn ($dayRows) => $dayRows->sortByDesc('id')->first())
            ->sortBy(fn ($row) => $row->created_at->timestamp ?? 0)
            ->values();
    }

    /**
     * Match cropYear column to requested year: exact value, or values like "2024-25" when request is "2024".
     */
    private function applyLivePriceCropYearMatch($query, $yearParam): void
    {
        $y = trim((string) $yearParam);
        if ($y === '') {
            return;
        }

        $query->where(function ($q) use ($y) {
            $q->where('cropYear', $y);
            if (preg_match('/^\d{4}$/', $y)) {
                $q->orWhere('cropYear', 'like', $y.'-%')
                    ->orWhere('cropYear', 'like', $y.'/%');
            }
        });
    }

    /**
     * Match getpriceByTimePeriod: form_name in DB often matches "STEAM (GRADE A+)" while the URL
     * segment may use underscores (STEAM_(GRADE_A+)). Try exact, underscore→space, and collapsed spaces.
     */
    private function resolveRiceFormForProductType(string $decodedRiceType, string $productType): ?RiceForm
    {
        $decodedRiceType = trim($decodedRiceType);
        $fromUnderscores = implode(' ', explode('_', $decodedRiceType));
        $collapsed = preg_replace('/\s+/', ' ', str_replace('_', ' ', $decodedRiceType));

        $candidates = array_values(array_unique(array_filter([
            $decodedRiceType,
            $fromUnderscores,
            $collapsed,
        ])));

        foreach ($candidates as $formName) {
            $row = RiceForm::query()
                ->where('form_name', $formName)
                ->where('type', $productType)
                ->where('status', 1)
                ->first();
            if ($row) {
                return $row;
            }
        }

        foreach ($candidates as $formName) {
            $row = RiceForm::query()
                ->where('form_name', $formName)
                ->where('type', $productType)
                ->first();
            if ($row) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Shared validation rules for SubmitSellQuery / SubmitSellQueryWeb (multipart, optional images).
     */
    private function rulesInrSellQuerySubmit(): array
    {
        return array_merge($this->rulesTradeQueryHierarchyIds(), [
            'userId' => ['required', 'integer', 'exists:users,id'],
            'changePackingType' => ['required'],
            'quantity' => ['required'],
            'offerPrice' => ['required'],
            'contactperson' => ['nullable', 'string', 'max:255'],
            'contactMobile' => ['nullable', 'string', 'max:64'],
            'warehouselocation' => ['nullable', 'string', 'max:500'],
            'riceSize' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:32'],
            'packageImageFile' => ['nullable', 'file', 'max:15360'],
            'uncookedFile' => ['nullable', 'file', 'max:15360'],
            'cookedImageFile' => ['nullable', 'file', 'max:15360'],
            'extra_file' => ['nullable', 'file', 'max:15360'],
        ], $this->rulesFarmingWebId(), $this->rulesOptionalReportUpload(), $this->rulesValidDaysForSellQuery());
    }

    /**
     * Valid till datetime for sell / future sell query APIs (stored in validDays column).
     */
    private function rulesValidDaysForSellQuery(): array
    {
        return [
            'validDays' => [
                'required',
                'string',
                'max:64',
                function (string $attribute, $value, \Closure $fail): void {
                    if ($this->normalizeValidDaysInputForRequest($value) === null) {
                        $fail(__('validation.date', ['attribute' => 'valid till']));
                    }
                },
            ],
        ];
    }

    private function validDaysAttributeNames(): array
    {
        return [
            'validDays' => 'valid till',
        ];
    }

    /**
     * Map aliases and normalize validDays to Y-m-d H:i:s before validation.
     */
    private function mergeValidDaysInputAliases(Request $request): void
    {
        if (! $request->filled('validDays')) {
            foreach (['validTill', 'validity', 'valid_till', 'validDaysDatetime', 'valid_till_datetime'] as $field) {
                if ($request->filled($field)) {
                    $request->merge(['validDays' => $request->input($field)]);
                    break;
                }
            }
        }

        if ($request->has('validDays') && $request->input('validDays') !== null && $request->input('validDays') !== '') {
            $normalized = $this->normalizeValidDaysInputForRequest($request->input('validDays'));
            if ($normalized !== null) {
                $request->merge(['validDays' => $normalized]);
            }
        }
    }

    /**
     * Accept datetime strings (and legacy day-count integers) for validDays.
     */
    private function normalizeValidDaysInputForRequest($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $tz = config('app.timezone', 'Asia/Kolkata');

        if (preg_match('/^\d{1,3}$/', (string) $value)) {
            $intVal = (int) $value;
            if ($intVal >= 1 && $intVal <= 365) {
                return Carbon::now($tz)->addDays($intVal)->endOfDay()->format('Y-m-d H:i:s');
            }
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^\d{10,13}$/', $raw)) {
            $ts = strlen($raw) > 10 ? (int) substr($raw, 0, 10) : (int) $raw;

            return Carbon::createFromTimestamp($ts, $tz)->format('Y-m-d H:i:s');
        }

        $formats = [
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d\TH:i:s',
            'Y-m-d\TH:i',
            'Y-m-d',
            'd-m-Y H:i:s',
            'd-m-Y H:i',
            'd-m-Y',
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'd/m/Y',
            'm/d/Y H:i:s',
            'm/d/Y H:i',
        ];

        foreach ($formats as $format) {
            try {
                $dt = Carbon::createFromFormat($format, $raw, $tz);
                if ($dt !== false) {
                    return $dt->format('Y-m-d H:i:s');
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        try {
            return Carbon::parse($raw, $tz)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveValidDaysForQuerySave(Request $request): string
    {
        $normalized = $this->normalizeValidDaysInputForRequest($request->input('validDays'));

        return $normalized ?? Carbon::now(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d H:i:s');
    }

    /**
     * Add formatted validTill display string; validDays holds datetime (Y-m-d H:i:s).
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection  $queries
     * @return \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection
     */
    private function formatSellQueryCollectionValidDays($queries)
    {
        return $queries->map(function ($query) {
            $raw = $query->validDays ?? null;
            if ($raw !== null && $raw !== '') {
                try {
                    $dt = Carbon::parse($raw)->timezone('Asia/Kolkata');
                    $query->setAttribute('validDays', $dt->format('Y-m-d H:i:s'));
                    $query->setAttribute('validTill', $dt->format('d-m-Y, g:i A'));
                } catch (\Throwable $e) {
                    $query->setAttribute('validTill', (string) $raw);
                }
            } else {
                $query->setAttribute('validTill', '');
            }

            return $query;
        });
    }

    /**
     * Optional report upload for sell & future sell (binary PDF / JPEG / JPG / PNG only).
     */
    private function rulesOptionalReportUpload(): array
    {
        $fileRule = ['nullable', 'file', 'max:15360', 'mimes:pdf,jpg,jpeg,png'];

        return [
            'report_file' => $fileRule,
            'upload_report' => $fileRule,
            'report' => $fileRule,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function allowedReportFileExtensions(): array
    {
        return ['pdf', 'jpg', 'jpeg', 'png'];
    }

    private function isAllowedReportFile(string $originalName, ?string $tmpPath = null): bool
    {
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (! in_array($ext, $this->allowedReportFileExtensions(), true)) {
            return false;
        }

        if ($tmpPath === null || ! is_readable($tmpPath)) {
            return true;
        }

        $mime = @mime_content_type($tmpPath);
        if ($mime === false) {
            return true;
        }

        return in_array($mime, ['application/pdf', 'image/jpeg', 'image/png'], true);
    }

    /**
     * Store optional report file; field name: report_file (aliases: upload_report, report).
     */
    private function storeOptionalReportUpload(Request $request): ?string
    {
        foreach (['report_file', 'upload_report', 'report'] as $field) {
            if ($request->hasFile($field)) {
                $uploaded = $request->file($field);
                if ($uploaded instanceof UploadedFile && $uploaded->isValid()) {
                    if (! $this->isAllowedReportFile($uploaded->getClientOriginalName(), $uploaded->getPathname())) {
                        continue;
                    }

                    return $this->persistInrQueryUploadedFile($uploaded);
                }
            }

            if (! empty($_FILES[$field]['tmp_name']) && (int) ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $name = (string) $_FILES[$field]['name'];
                $tmp = (string) $_FILES[$field]['tmp_name'];
                if (! $this->isAllowedReportFile($name, $tmp)) {
                    continue;
                }

                return $this->persistInrQueryUploadFromTmp($name, $tmp);
            }
        }

        return null;
    }

    private function persistInrQueryUploadedFile(UploadedFile $file): string
    {
        if (! file_exists('uploads')) {
            mkdir('uploads', 0755, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
        if (! in_array($ext, $this->allowedReportFileExtensions(), true)) {
            $ext = 'pdf';
        }

        $fileName = 'report_' . uniqid('', true) . '.' . $ext;
        $file->move('uploads', $fileName);

        return $fileName;
    }

    private function persistInrQueryUploadFromTmp(string $originalName, string $tmpPath): string
    {
        if (! file_exists('uploads')) {
            mkdir('uploads', 0755, true);
        }

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (! in_array($ext, $this->allowedReportFileExtensions(), true)) {
            $ext = 'pdf';
        }

        $fileName = 'report_' . uniqid('', true) . '.' . $ext;
        move_uploaded_file($tmpPath, 'uploads/' . $fileName);

        return $fileName;
    }

    /**
     * Farming type ids for web buy/sell/future query APIs (TradeQueriesINR::$farmingTypeWeb).
     */
    private function rulesFarmingWebId(): array
    {
        $allowed = array_keys(TradeQueriesINR::$farmingTypeWeb);

        return [
            'farming' => ['nullable', 'integer', Rule::in($allowed)],
            'farmingType' => ['nullable', 'integer', Rule::in($allowed)],
            'farming_type' => ['nullable', 'integer', Rule::in($allowed)],
        ];
    }

    private function farmingWebAttributeNames(): array
    {
        return [
            'farming' => 'farming type',
            'farmingType' => 'farming type',
            'farming_type' => 'farming type',
        ];
    }

    /**
     * Resolve farming id (1–4) from request; stored in query `farming` column.
     */
    private function resolveFarmingForQuerySave(Request $request): ?string
    {
        foreach (['farming', 'farmingType', 'farming_type'] as $key) {
            if (! $request->has($key)) {
                continue;
            }
            $value = $request->input($key);
            if ($value === null || $value === '') {
                continue;
            }

            return (string) (int) $value;
        }

        return null;
    }

    /**
     * Trades created from a user's buy/sell/future query (matched by tradeType + queryId).
     */
    private function personalLinkedTradesQuery(array $queryIds, int $tradeType)
    {
        return TradeQueriesINR::query()
            ->where('tradeType', $tradeType)
            ->where('queryId', '>', 0)
            ->whereIn('queryId', $queryIds);
    }

    private function countPersonalLinkedTrades($userId, ?int $status = null): int
    {
        $pairs = [
            [1, BuyQueriesINR::class],
            [2, SellQueriesINR::class],
            [3, FutureBuyQueriesINR::class],
            [4, FutureSellQueriesINR::class],
        ];

        $count = 0;
        foreach ($pairs as [$tradeType, $modelClass]) {
            $queryIds = $modelClass::where('created_by', $userId)->pluck('id')->all();
            if ($queryIds === []) {
                continue;
            }

            $q = $this->personalLinkedTradesQuery($queryIds, $tradeType);
            if ($status !== null) {
                $q->where('status', $status);
            }
            $count += $q->count();
        }

        return $count;
    }

    /**
     * Queries moved to trade (status 2) or converted to a trade record.
     */
    private function countPersonalMovedToTradeQueries($userId): int
    {
        $pairs = [
            [1, BuyQueriesINR::class],
            [2, SellQueriesINR::class],
            [3, FutureBuyQueriesINR::class],
            [4, FutureSellQueriesINR::class],
        ];

        $count = 0;
        foreach ($pairs as [$tradeType, $modelClass]) {
            $linkedQueryIds = TradeQueriesINR::query()
                ->where('tradeType', $tradeType)
                ->where('queryId', '>', 0)
                ->pluck('queryId');

            $q = $modelClass::where('created_by', $userId)->where(function ($query) use ($linkedQueryIds) {
                $query->where('status', 2);
                if ($linkedQueryIds->isNotEmpty()) {
                    $query->orWhereIn('id', $linkedQueryIds);
                }
            });

            $count += $q->count();
        }

        return $count;
    }

    /**
     * Category, quality, form, and grade must be real selections — not null, empty, 0, or literal "null"/"undefined".
     */
    private function rulesTradeQueryHierarchyIds(bool $requireGrade = true): array
    {
        $mustSelect = $this->ruleTradeQueryMustSelectValue();

        $rules = [
            'selectedQualityTypeInt' => array_merge(['bail'], $mustSelect),
            'quality' => array_merge(['bail'], $mustSelect),
            'qualityForm' => array_merge(['bail'], $mustSelect),
        ];

        $rules['selectedGrade'] = $requireGrade
            ? array_merge(['bail'], $mustSelect)
            : ['bail', 'nullable'];

        return $rules;
    }

    /**
     * Treat unset / placeholder hierarchy values as empty (used when grade is optional).
     */
    private function coalesceEmptyTradeQuerySelection($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value)) {
            $t = strtolower(trim($value));
            if ($t === '' || $t === 'null' || $t === 'undefined') {
                return null;
            }
        }
        if ($value === 0 || $value === '0' || $value === 0.0) {
            return null;
        }

        return (string) $value;
    }

    private function ruleTradeQueryMustSelectValue(): array
    {
        $labels = $this->tradeQueryHierarchyAttributeNames();

        return [
            'required',
            function (string $attribute, $value, \Closure $fail) use ($labels): void {
                $label = $labels[$attribute] ?? str_replace('_', ' ', $attribute);
                if ($value === null || $value === '') {
                    $fail(__('validation.required', ['attribute' => $label]));

                    return;
                }
                if (is_string($value)) {
                    $t = strtolower(trim($value));
                    if ($t === '' || $t === 'null' || $t === 'undefined') {
                        $fail(__('validation.required', ['attribute' => $label]));

                        return;
                    }
                }
                if ($value === 0 || $value === '0' || $value === 0.0) {
                    $fail(__('validation.required', ['attribute' => $label]));
                }
            },
        ];
    }

    private function tradeQueryHierarchyAttributeNames(): array
    {
        return [
            'selectedQualityTypeInt' => 'category',
            'quality' => 'quality',
            'qualityForm' => 'quality form',
            'selectedGrade' => 'sub quality',
        ];
    }

    private function tradeQueryValidationFailedResponse(ValidatorContract $validator): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => 'Validation error.',
            'errors' => $validator->errors(),
        ], 422);
    }

    /**
     * @return int[]
     */
    private function resolveMappedRiceFormIds(int $riceId, string $riceTypeRaw): array
    {
        $formIds = AvgLengthMap::query()
            ->where('rice_name_id', $riceId)
            ->pluck('form_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($formIds !== []) {
            return $formIds;
        }

        $formIdSet = [];
        $maps = WebRiceFormMap::query()
            ->where('rice_name_id', $riceId)
            ->when($riceTypeRaw !== '', function ($q) use ($riceTypeRaw) {
                $q->where(function ($q2) use ($riceTypeRaw) {
                    $q2->where('rice_type', $riceTypeRaw)->orWhereNull('rice_type');
                });
            })
            ->get(['form_ids']);

        foreach ($maps as $map) {
            foreach ($this->normalizeWebRiceFormIds($map->form_ids) as $formId) {
                $formIdSet[$formId] = true;
            }
        }

        return array_keys($formIdSet);
    }

    /**
     * @return int[]
     */
    private function resolveMappedRiceWandIds(int $riceNameId, int $formId, string $riceTypeRaw): array
    {
        $map = AvgLengthMap::query()
            ->where('rice_name_id', $riceNameId)
            ->where('form_id', $formId)
            ->first();

        if ($map && is_array($map->wand_ids)) {
            $wandIds = array_values(array_unique(array_filter(array_map('intval', $map->wand_ids))));
            if ($wandIds !== []) {
                return $wandIds;
            }
        }

        $formMap = WebRiceFormMap::query()
            ->where('rice_name_id', $riceNameId)
            ->when($riceTypeRaw !== '', function ($q) use ($riceTypeRaw) {
                $q->where(function ($q2) use ($riceTypeRaw) {
                    $q2->where('rice_type', $riceTypeRaw)->orWhereNull('rice_type');
                });
            })
            ->where(function ($q) use ($formId) {
                $q->whereJsonContains('form_ids', $formId)
                    ->orWhereJsonContains('form_ids', (string) $formId)
                    ->orWhereRaw('CAST(form_ids AS UNSIGNED) = ?', [$formId]);
            })
            ->first();

        if (! $formMap || ! is_array($formMap->wand_ids)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $formMap->wand_ids))));
    }

    /**
     * @param  mixed  $formIds
     * @return int[]
     */
    private function normalizeWebRiceFormIds($formIds): array
    {
        if ($formIds === null || $formIds === '') {
            return [];
        }
        if (is_array($formIds)) {
            $out = [];
            foreach ($formIds as $value) {
                if (is_numeric($value)) {
                    $out[] = (int) $value;
                }
            }

            return array_values(array_unique($out));
        }
        if (is_numeric($formIds)) {
            return [(int) $formIds];
        }
        if (is_string($formIds)) {
            $decoded = json_decode($formIds, true);
            if (is_array($decoded)) {
                return $this->normalizeWebRiceFormIds($decoded);
            }
            if (is_numeric($formIds)) {
                return [(int) $formIds];
            }
        }

        return [];
    }
}
