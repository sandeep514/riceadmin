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


class ApiController extends Controller
{
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

    public function login(Request $request)
    {
        $userModel = User::where(['email' => $request->email , 'userType' => 1])->with(['role_rel', 'role_rel_usd'])->first();
        if ($userModel == null) {
            $userModel = User::where(['mobile' => $request->email , 'userType' => 1])->with(['role_rel', 'role_rel_usd'])->first();
        }

        if ($userModel == null) {
            return response()->json(['status' => 'error', 'message' => 'Wrong user detail']);
        }


        $oldPassword = $userModel->password;

        if (Hash::check($request->password, $oldPassword)) {
            $random_token = Str::random(60);

            if ($userModel->is_usd_active == 0) {
                if ($userModel->is_INR_active == 0) {

                    $checkuser = User::where(['email' => $request->email , 'userType' => 1])->first();
                    if ($checkuser == null) {
                        dd("here");
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
                User::where(['email' => $request->email, 'status' => 1 ,'userType' => 1])->update(['api_token' => $random_token]);

                $Newotp = $userModel->otp;
                $mobile = $userModel->mobile;
                file_get_contents('http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto=' . $mobile . '&message=Thank+you+for+registering+on+SNTC+Rice+Live+Pricing+App.+Your+OTP+Code+is+'.$Newotp.'.+SNTCAL&PEID=1701172916686910712&templateid=1707172924575773908');

                if ($userModel->email != null) {
                    $response = MailController::generateMailForOTPThanks($userModel->email, 'no@replay.in', 'SNTC GROUP', 'Thank you for registering on SNTC Rice Live Pricing App.', 'Thank you for registering on SNTC Rice Live Pricing App.', $Newotp);
                }

                return response()->json(['status' => 'success', 'user' => $userModel]);
            }
            return response()->json(['status' => 'success', 'user' => $userModel]);
        } else {
            return response()->json(['status' => 'error', 'test' => 1, 'message' => 'Wrong user detail']);
        }
    }



    public function sendOTP($number, $isOTP = false)
    {


        $otp = rand(1111, 9999);
        $user = User::where('mobile', $number)->where('status', 1)->first();
        if ($user != null) {
            User::where('mobile', $number)->update(['otp' => $otp]);

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
                User::where('mobile', $number)->update(['otp' => $otp]);
                if ($user->email != null) {
                    $response = MailController::generateMailForOTP($user->email, 'no@replay.in', 'SNTC GROUP', null, 'SNTC OTP Verification ', $otp);
                }
            }

            return response()->json(['error' => null, 'data' => $otp, 'mailResponse' => $response, 'user' => $user], 200);
        } else {
            return response()->json(['error' => 'No record available for ' . $number, 'user' => $user], 500);
        }
    }


    public function resendOTP($number)
    {
        $otp = rand(1111, 9999);
        $user = User::where('mobile', $number)->first();
        User::where('mobile', $number)->update(['otp' => $otp]);


        file_get_contents('http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto=' . $mobile . '&message=Thank+you+for+registering+on+SNTC+Rice+Live+Pricing+App.+Your+OTP+Code+is+'.$Newotp.'.+SNTCAL&PEID=1701172916686910712&templateid=1707172924575773908');
        if ($user->email != null) {
            $response = MailController::generateMailForOTPThanks($user->email, 'no@replay.in', 'SNTC GROUP', 'Thank you for registering on SNTC Rice Live Pricing App.', 'Thank you for registering on SNTC Rice Live Pricing App.', $otp);
        }

        return response()->json(['error' => null, 'data' => $otp, 'mailResponse' => $response], 200);
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
    //                 'lastUpdatedDate' => $lastRecord->created_at->format('d-m-Y | H:i A'),
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
    //             'latest' => $lastRecord->created_at->format('d-m-Y | H:i'),
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
                    'lastUpdatedDate' => $lastRecord->updated_at->format('d-m-Y | H:i A'),
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
                'latest' => $lastRecord->created_at->format('d-m-Y | H:i'),
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
                $latestEnteredRecord = LivePrice::orderBy('id' , 'desc')->first();
                return response()->json([
                    'errors' => null,
                    'prices' => $myNewData,
                    'latest' => ($startYear != '' && $endYear != '')? $recordDate : $lastRecord->created_at->format('Y-m-d'),
                    'lastUpdatedDate' => ($latestEnteredRecord->updated_at) ? $latestEnteredRecord->updated_at->format('d-m-Y | H:i A') : '',
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
                'latest' => $lastRecord->created_at->format('d-m-Y | H:i'),
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

        // latest entered record for this cropYear
        $lastRecord = LivePrice::query()
            ->where('name', '!=', '0')
            ->where('form', '!=', '0')
            ->whereNotNull('min_price')
            ->whereNotNull('max_price')
            ->where('state', $state)
            ->when($cropYear, fn($q) => $q->where('cropYear', $cropYear))
            // ->whereDate('created_at', '<=', $cropYear)
            ->latest('id')
            ->first();

            
        if ($lastRecord) {
            // previous record (for comparison)
            $lastToLastDate = LivePrice::query()
                ->where('name', '!=', '0')
                ->where('form', '!=', '0')
                ->whereNotNull('min_price')
                ->whereNotNull('max_price')
                ->where('state', $state)
                ->when($cropYear, fn($q) => $q->where('cropYear', $cropYear))
                // ->whereDate('created_at', '<', $lastRecord->created_at->format('Y-m-d'))
                ->latest()
                ->first();
                // dd([
                //     $lastRecord->created_at->format('Y-m-d'),
                //     $lastToLastDate->created_at->format('Y-m-d')
                // ]);
            if ($lastToLastDate) {
                // main query
                
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
                    ->addSelect([
                        // 'closing_opening' => DB::table('live_price_closing')
                        //     ->select('opening')
                        //     ->whereColumn('live_price_closing.name', 'live_prices.name')
                        //     ->whereColumn('live_price_closing.form', 'live_prices.form')
                        //     ->whereRaw('live_price_closing.state COLLATE utf8mb4_unicode_ci = live_prices.state COLLATE utf8mb4_unicode_ci')
                        //     ->whereColumn('live_price_closing.cropYear', 'live_prices.cropYear')
                        //     ->orderByDesc('id')
                        //     ->limit(1),
                    
                        'closing_data' => DB::table('live_price_closing')
                            ->select('closing')
                            ->whereColumn('live_price_closing.name', 'live_prices.name')
                            ->whereColumn('live_price_closing.form', 'live_prices.form')
                            ->whereRaw('live_price_closing.state COLLATE utf8mb4_unicode_ci = live_prices.state COLLATE utf8mb4_unicode_ci')
                            ->whereColumn('live_price_closing.cropYear', 'live_prices.cropYear')
                            ->orderByDesc('id')
                            ->limit(1),
                    ])
                    ->whereNotNull('min_price')
                    ->whereNotNull('max_price')
                    ->where('state', $state)
                    ->when($cropYear, fn($q) => $q->where('cropYear', $cropYear))
                    ->whereIn(DB::raw('date(live_prices.created_at)'), [
                        $lastRecord->created_at->format('Y-m-d'),
                        $lastToLastDate->created_at->format('Y-m-d')
                    ])
                    ->get();

                   
                // process
                foreach ($data->sortBy('name_rel.order') as $v) {
                    $replaceHignfn = explode('-', $v->name_rel->type);
                    $implodeUnderscore = implode('_', $replaceHignfn);
                    $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                }

                $fiilteredProcessedData = [];
                foreach ($data->sortBy('form_rel.order') as $v) {
                    $replaceHignfn = explode('-', $v->name_rel->type);
                    $implodeUnderscore = implode('_', $replaceHignfn);
                    $fiilteredProcessedData[$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                }

                foreach ($processedData as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $processedData[$k][$kk] = $fiilteredProcessedData[$kk];
                    }
                }

                $latstRecord = $lastRecord->created_at->format('Y-m-d');

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

                $latestEnteredRecord = LivePrice::orderBy('id', 'desc')->first();

                return response()->json([
                    'errors' => null,
                    'prices' => $myNewData,
                    'latest' => $lastRecord->created_at->format('Y-m-d'),
                    'lastUpdatedDate' => ($latestEnteredRecord->updated_at) ? $latestEnteredRecord->updated_at->format('d-m-Y | H:i A') : '',
                    'oldDate' => $lastToLastDate->created_at->format('Y-m-d')
                ]);
            }

            // if only latest record (no previous date found)
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
                ->whereIn(DB::raw('date(live_prices.created_at)'), [
                    $lastRecord->created_at->format('Y-m-d'),
                    $lastToLastDate->created_at->format('Y-m-d')
                ])
                ->when($cropYear, fn($q) => $q->where('cropYear', $cropYear))
                ->get();

            foreach ($prices as $v) {
                $replaceHignfn = explode('-', $v->name_rel->type);
                $implodeUnderscore = implode('_', $replaceHignfn);
                $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
            }

            return response()->json([
                'errors' => null,
                'prices' => json_encode($processedData),
                'latest' => $lastRecord->created_at->format('d-m-Y | H:i'),
                'oldDate' => ''
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
    //             'lastUpdatedDate' => ($latestEnteredRecord->updated_at) ? $latestEnteredRecord->updated_at->format('d-m-Y | H:i A') : '',
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
    //             'lastUpdatedDate' => ($latestEnteredRecord->updated_at)? $latestEnteredRecord->updated_at->format('d-m-Y | H:i A') : '',
    //             // 'oldDate' => $lastToLastDate[0]->created_at->format('Y-m-d')
    //         ]);
    // }
    //6 feb 2026 change by sandeep for form_order sort

    public function getPricesWeb(Request $request ,$state, $ricetype)
    {
        $LivePriceStatusMessage = LivePriceStatusMessage::orderBy('id' , 'desc')->first();
        $latestCropYearRecord = LivePrice::orderBy('cropYear' , 'desc')->first();
        $latestCropYear = $latestCropYearRecord->cropYear;

        $todayDate = Carbon::now();
        $cropYear = (request()->has('year')) ? request()->get('year') : $latestCropYear;

        $year = ($todayDate->year >= $latestCropYear) ? $todayDate->year : $cropYear;
        $date = $todayDate->day;
        $month = $todayDate->month;

        $lastEnteredRecord = Carbon::createFromDate($year, $month, $date)->format('Y-m-d');

        $lastRecord = LivePrice::query()
            ->where('name', '!=', '0')
            ->where('form', '!=', '0')
            ->whereNotNull('min_price')
            ->whereNotNull('max_price')
            ->where('state', $state)
            ->whereDate('created_at' , $lastEnteredRecord)
            ->where('cropYear' , $cropYear)
            ->latest('id');

        if(!$lastRecord->exists()){
            $lastRecord = LivePrice::query()
                ->where('name', '!=', '0')
                ->where('form', '!=', '0')
                ->whereNotNull('min_price')
                ->whereNotNull('max_price')
                ->where('state', $state)
                ->where('cropYear' , $cropYear)
                ->whereDate('created_at' ,'<', $lastEnteredRecord)
                ->latest('id');
        }

        $lastEnteredRecord = $lastRecord->first();

        /*
        |--------------------------------------------------------------------------
        | MAIN DATA QUERY
        |--------------------------------------------------------------------------
        */

        $data = LivePrice::query()
            ->has('name_rel')
            ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype)->orderBy('order', "ASC"))
            ->with([
                'name_rel',
                'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('order', "ASC")
            ])
            ->withCount([
                'trades as tradeCount' => function ($q) {
                    $q->whereColumn('trade_query_milestone3.qualityFormLinkWithLivePrice', 'live_prices.form')
                      ->whereColumn('trade_query_milestone3.stateLinkWithLivePrice' , 'live_prices.state');
                }
            ])
            ->whereNotNull('min_price')
            ->whereNotNull('max_price')
            ->where('state', $state)
            ->where('cropYear' , $cropYear)
            ->orderBy('name_order')
            ->orderBy('form_order')
            ->whereDate('created_at',$lastEnteredRecord->created_at)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ✅ EXTRA SORT (form_order + name_order)
        |--------------------------------------------------------------------------
        */

        $data = $data->sortBy([
            ['name_order', 'asc'],
            ['form_order', 'asc'],
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

            $stateOrder = LivePrice::distinct('state')->orderBy('state_order')->pluck('state' , 'state_order')->toArray();

            $livePricesClosingOpening = LivePricesOpeningClosing::query()
                ->select('live_price_closing.*')
                ->join('rice_names as rn', 'rn.id', '=', 'live_price_closing.name')
                ->join('rice_forms as rf', 'rf.id', '=', 'live_price_closing.form')

                ->whereIn('live_price_closing.id', $latestIds)
                ->where('rn.type', $ricetype)
                ->where('rf.type', $ricetype)

                /* 1️⃣ State custom order */
                ->orderByRaw(
                    "FIELD(live_price_closing.state, '" . implode("','", $stateOrder) . "')"
                )

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

            // If created_at or updated_at is not today, treat as not updated by admin
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
            foreach ($temp[$implodeUnderscore] as $riceType => $forms) {

                uksort($forms, function ($a, $b) use ($data) {
                    $aOrder = optional(
                        $data->first(fn($x) => $x->form_rel->form_name == $a)
                    )->form_order ?? 999;

                    $bOrder = optional(
                        $data->first(fn($x) => $x->form_rel->form_name == $b)
                    )->form_order ?? 999;

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

        $latestEnteredRecord = LivePrice::orderBy('id', 'desc')->first();

        return response()->json([
            'errors' => null,
            'livePriceStatusMessage' => $LivePriceStatusMessage,
            'prices' => $processedData,
            'closing' => [$ricetype => $livePricesClosingOpening],
            'latest' => Carbon::parse($lastEnteredRecord->created_at)->format('Y-m-d'),
            'lastUpdatedDate' => ($latestEnteredRecord->updated_at)
                ? $latestEnteredRecord->updated_at->format('d-m-Y | H:i A')
                : '',
        ]);
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

        $state = base64_decode($state);
        $riceType = base64_decode($riceType);
        $rice = base64_decode($rice);
        $timePeriod = base64_decode($timePeriod);
        
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

        $productType = RiceName::select('type')->where('name', $rice)->first();

        $riceName = RiceName::where('name', $rice)->first();
        if( $request->has('year') ){
            // $explodeYear = explode('-' , $request->year);
            // $fromYear = $explodeYear[0];
            $year = $request->year;

        }

        $explodeRiceType = explode('_', $riceType);
        $implodeRiceType = implode(' ', $explodeRiceType);
        $type = RiceForm::select('id')->where('form_name', $implodeRiceType)->where('type', $productType->type)->first();

        $fromDate = $todayDate->format('y-m-d');

        $explodeTime = explode('_',  $timePeriod  );
        if( $request->has('year') ){
            $prices = LivePrice::where('name', $riceName->id)->where('form', $type->id)->with([
                'name_rel',
                'form_rel' => function ($query) use ($riceType) {
                    return $query->where('type', $riceType)->get();
                }
            // ])->where(['state' => $state])->whereYear('created_at', $year)->get();
            ])->where(['state' => $state])->where('cropYear', $year)->get();
        }else{
             if( count($explodeTime) > 1 ){
                $prices = LivePrice::where('name', $riceName->id)->where('form', $type->id)->with([
                    'name_rel',
                    'form_rel' => function ($query) use ($riceType) {
                        return $query->where('type', $riceType)->get();
                    }
                ])->where(['state' => $state])->where(DB::raw('date(created_at)'), '<=', $fromDate)->get();

            }else{
                $prices = LivePrice::where('name', $riceName->id)->where('form', $type->id)->with([
                    'name_rel',
                    'form_rel' => function ($query) use ($riceType) {
                        return $query->where('type', $riceType)->get();
                    }
                ])->where(['state' => $state])->whereBetween(DB::raw('date(created_at)'), [$todayDate->subDays($timePeriod) , $fromDate ])->get();
            }
        }

        foreach ($prices as $k => $v) {
            $created_at[] = strtotime($v->created_at->format('y-m-d'));
        }
        foreach ($prices as $key => $value) {
            $max_price[] = $value->max_price;
        }

        $combine = array_combine($created_at, $max_price);

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
        $responseData = ['errors' => null, 'date' => $created_at, 'prices' => $arrayValuesPrices, 'combinedData' => $combinedData, 'productType' => $productType , 'lowValue' => $lowValue,'lowDate' => $lowDate  , 'highDate' => $highDate , 'highValue' => $highValue , 'constantValue' => $constantValue,'maxCountConstant' => $maxCount];

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

    public function getGalleryData()
    {
        $gallery = Gallery::get()->groupBy('type');
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
            // $this->sendOTP($request->mobile,false);
            return response()->json(['error' => "success", 'data' => []], 200);
        } else {
            return response()->json(['error' => "Wrong OTP.", 'data' => []], 500);
        }
    }

    public function verifyOTP($number, $otp)
    {
        $user = User::where(['mobile' => $number, 'otp' => $otp,'userType' => 1])->get();
        if ($user->count() > 0) {
            // $this->sendOTP($user[0]->mobile , true);

            return response()->json(['error' => null, 'data' => $otp], 200);
        } else {
            return response()->json(['error' => null, 'data' => null], 500);
        }
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
        $latestCropYearRecord = LivePrice::orderBy('cropYear' , 'desc')->first();
        $latestCropYear = $latestCropYearRecord->cropYear;

        $states = [];
        $ricetype = 'basmati';

        $todayDate = Carbon::now();

        $cropYear = (request()->has('year')) ? request()->get('year') : $latestCropYear;
        $year = ( $todayDate->year >= $latestCropYear) ? $todayDate->year : $cropYear ;

        $date = $todayDate->day;
        $month = $todayDate->month;

        $lastEnteredRecord = Carbon::createFromDate($year, $month, $date)->format('Y-m-d');

        $lastRecord = LivePrice::query()
                ->where('name', '!=', '0')
                ->where('form', '!=', '0')
                ->whereNotNull('min_price')
                ->whereNotNull('max_price')
                ->whereDate('created_at' , $lastEnteredRecord)
                ->where('cropYear' , $cropYear)
                ->latest('id');

        if( !$lastRecord->exists() ){
            $lastRecord = LivePrice::query()
                ->where('name', '!=', '0')
                ->where('form', '!=', '0')
                ->whereNotNull('min_price')
                ->whereNotNull('max_price')
                ->where('cropYear' , $cropYear)
                ->whereDate('created_at' ,'<', $lastEnteredRecord)
                ->latest('id');

        }

        $lastEnteredRecord = $lastRecord->first();

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
                ->whereNotNull('min_price')
                ->whereNotNull('max_price')
                ->where('cropYear' , $cropYear)
                // ->orderBy('name_order')
                ->orderBy('state_order')
                ->whereDate('created_at',$lastEnteredRecord->created_at)->get();

                $livePricesClosingOpening = LivePricesOpeningClosing::select(["id","trade_for","farming_type","name","form","cropYear","state","opening","closing"])
                    ->where('cropYear' , $cropYear)
                    ->where(function ($q) {
                        $q->whereNotNull('closing')->where('closing', '!=', '');
                    })
                    ->whereHas('name_rel', fn($q) => $q->where('type', $ricetype))
                    // ->whereHas('form_rel')
                    ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
                    ->with([
                        'name_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC"),
                        // 'form_rel'
                        'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
                    ])
                    // ->orderBy('state_order')
                    ->get();
                
                $sortArray = LivePrice::distinct('state')->orderBy('state_order')->pluck('state' , 'state_order')->toArray();
                // dd($sortArray);
                $closingCropSates = $livePricesClosingOpening->pluck('state')->toArray();

                $hasClosingName = $livePricesClosingOpening->pluck('name');
                $hasClosingForm = $livePricesClosingOpening->pluck('form');

                $hasOpenigClosingConcade = [];
                foreach ($hasClosingName as $index => $key) {
                    $hasOpenigClosingConcade[] = strtolower($key . '_' . $hasClosingForm[$index]);
                }

            $processedData = [];
            $temp = [];

            if( $cropYear == 2023 ){
                foreach ($data as $v) {
                    if (!str_contains(strtolower($v['form_rel']['form_name']), 'new crop') && $cropYear == "2023") {

                          if ( count($hasOpenigClosingConcade) > 0 ){
                            $combineNameForm = $v->name.'_'.$v->form;
                            if( !in_array($combineNameForm, $hasOpenigClosingConcade) ) {

                                $states[] = $v->state;
                            }
                        }else{

                            $states[] = $v->state;
                        }
                    }
                }
                
            }else{
                foreach ($data as $v) {
                  if ( count($hasOpenigClosingConcade) > 0 ){
                    $combineNameForm = $v->name.'_'.$v->form;
                    if( !in_array($combineNameForm, $hasOpenigClosingConcade) ) {
                        $states[] = $v->state;
                    }
                }else{
                    $states[] = $v->state;
                }

                }
            }

            $closingAndOpenCropStates = array_merge($states , $closingCropSates);

            $states = array_values(array_unique($closingAndOpenCropStates));
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
            
            ksort($sortedMap, SORT_NATURAL);
            $states = array_values($sortedMap);
            return response()->json(['error' => null, 'data' => $states], 200);
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
        $latestCropYearRecord = LivePrice::orderBy('cropYear' , 'desc')->first();
        $latestCropYear = $latestCropYearRecord->cropYear;

        $states = [];
        $ricetype = 'non-basmati';

        $todayDate = Carbon::now();

        $cropYear = (request()->has('year')) ? request()->get('year') : $latestCropYear;

        $year = ( $todayDate->year >= $latestCropYear) ? $todayDate->year : $cropYear ;
        $date = $todayDate->day;
        $month = $todayDate->month;


        $lastEnteredRecord = Carbon::createFromDate($year, $month, $date)->format('Y-m-d');

        $lastRecord = LivePrice::query()->orderBy('state_order')
                ->where('name', '!=', '0')
                ->where('form', '!=', '0')
                ->whereNotNull('min_price')
                ->whereNotNull('max_price')
                ->whereDate('created_at' , $lastEnteredRecord)
                ->where('cropYear' , $cropYear)
                ->latest('id');

        if( !$lastRecord->exists() ){
            $lastRecord = LivePrice::query()->orderBy('state_order')
                ->where('name', '!=', '0')
                ->where('form', '!=', '0')
                ->whereNotNull('min_price')
                ->whereNotNull('max_price')
                ->where('cropYear' , $cropYear)
                ->whereDate('created_at' ,'<', $lastEnteredRecord)
                ->latest('id');
        }

        $lastEnteredRecord = $lastRecord->first();
        // dd(LivePrice::query()->orderBy('state_order')->get());
        $data = LivePrice::query()->orderBy('state_order')
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
                ->whereNotNull('min_price')
                ->whereNotNull('max_price')
                ->where('cropYear' , $cropYear)
                ->orderBy('name_order')
                ->whereDate('created_at',$lastEnteredRecord->created_at)->get();


            $livePricesClosingOpening = LivePricesOpeningClosing::select(["id","trade_for","farming_type","name","form","cropYear","state","opening","closing"])
                ->where('cropYear' , $cropYear)
                ->where(function ($q) {
                    $q->whereNotNull('closing')->where('closing', '!=', '');
                })
                ->whereHas('name_rel', fn($q) => $q->where('type', $ricetype))
                // ->whereHas('form_rel')
                ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
                ->with([
                    'name_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC"),
                    // 'form_rel'
                    'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
                ])
                ->get();

            $closingCropSates = $livePricesClosingOpening->pluck('state')->toArray();

            $hasClosingName = $livePricesClosingOpening->pluck('name');
            $hasClosingForm = $livePricesClosingOpening->pluck('form');
            
            $sortArray = LivePrice::distinct('state')->orderBy('state_order')->pluck('state' , 'state_order')->toArray();

            $hasOpenigClosingConcade = [];
            foreach ($hasClosingName as $index => $key) {
                $hasOpenigClosingConcade[] = strtolower($key . '_' . $hasClosingForm[$index]);
            }

            $processedData = [];
            $temp = [];

            if( $cropYear == 2023 ){
                foreach ($data as $v) {
                    if (!str_contains(strtolower($v['form_rel']['form_name']), 'new crop') && $cropYear == "2023") {

                          if ( count($hasOpenigClosingConcade) > 0 ){
                            $combineNameForm = $v->name.'_'.$v->form;
                            if( !in_array($combineNameForm, $hasOpenigClosingConcade) ) {

                                $states[] = $v->state;
                            }
                        }else{

                            $states[] = $v->state;
                        }
                    }
                }
                
            }else{
                foreach ($data as $v) {
                  if ( count($hasOpenigClosingConcade) > 0 ){
                    $combineNameForm = $v->name.'_'.$v->form;
                    if( !in_array($combineNameForm, $hasOpenigClosingConcade) ) {
                        $states[] = $v->state;
                    }
                }else{
                    $states[] = $v->state;
                }

                }
            }

            $closingAndOpenCropStates = array_merge($states , $closingCropSates);

           $states = array_values(array_unique($closingAndOpenCropStates));
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
            $statesArray = ksort($sortedMap, SORT_NATURAL);
            $states = array_values($sortedMap);
            return response()->json(['error' => null, 'data' => $states], 200);

        /*
        |--------------------------------------------------------------------------
        | Convert temp structure to REQUIRED final response
        |--------------------------------------------------------------------------
        */

        // foreach ($temp[$ricetype] as $riceType => $forms) {

        //     $formArray = [];

        //     foreach ($forms as $formName => $dates) {
        //         $formArray[] = [
        //             $formName => $dates
        //         ];
        //     }

        //     $processedData[$ricetype][] = [
        //         $riceType => $formArray
        //     ];
        // }

        
        // $latestEnteredRecord = LivePrice::orderBy('id', 'desc')->first();

        // return response()->json([
        //         'errors' => null,
        //         'prices' => $processedData,
        //         'closing' => [$ricetype => $livePricesClosingOpening],
        //         'latest' => Carbon::parse($lastEnteredRecord->created_at)->format('Y-m-d'),
        //         'lastUpdatedDate' => ($latestEnteredRecord->updated_at) ? $latestEnteredRecord->updated_at->format('d-m-Y | H:i A') : '',
        //         // 'oldDate' => $lastToLastDate
        //     ]);





        dd(array_unique($states));


        $today = Carbon::now();
        $todayYear = $today->year;
        $cropYear = $request->year;
        $ricetype = 'non-basmati';

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

        $date = Carbon::parse($lastEnteredRecord)->format('d');
        $month = Carbon::parse($lastEnteredRecord)->format('m');

        $lastEnteredRecord = Carbon::createFromDate($cropYear, $month, $date)->format('Y-m-d');


        $livePricesClosingOpening = LivePricesOpeningClosing::select(["id","trade_for","farming_type","name","form","cropYear","state","opening","closing"])
                ->where('cropYear', $cropYear)
                ->where(function ($q) {
                    $q->whereNotNull('closing')->where('closing', '!=', '');
                })
                ->whereHas('name_rel', fn($q) => $q->where('type', $ricetype))
                ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
                ->with([
                    'name_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC"),
                    'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
                ])
                ->get();

        $closingRiceNameForm = [];
        $closingStates = [];
        foreach($livePricesClosingOpening as $k => $v){
            $closingRiceNameForm[] = $v->name.'_'.$v->form;
            $closingStates[] =  $v->state ;
        }


        $livePrice = LivePrice::query()
            ->has('name_rel')
            ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
            ->with([
                'name_rel',
                'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
            ])
            ->whereNotNull('min_price')
            ->whereNotNull('max_price')
            // ->where('state', $state)
            ->where('cropYear' , $cropYear)
            ->where(function ($q) {
                    $q->whereNull('closing')->orWhere('closing', '');
                })
            ->whereDate('created_at',$lastEnteredRecord);

            if( !$livePrice->exists() ){
                $lastlivePrice = LivePrice::query()
                    ->has('name_rel')
                    ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
                    ->with([
                        'name_rel',
                        'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
                    ])
                    ->whereNotNull('min_price')
                    ->whereNotNull('max_price')
                    // ->where('state', $state)
                    ->where('cropYear' , $cropYear)
                    ->where(function ($q) {
                            $q->whereNull('closing')->orWhere('closing', '');
                        })
                    ->whereDate('created_at','<',$lastEnteredRecord)->first();

                $lastAvaialbleRecordDate = $lastlivePrice->created_at;

                $lastlivePrice = LivePrice::query()
                    ->has('name_rel')
                    ->whereHas('form_rel', fn($q) => $q->where('type', $ricetype))
                    ->with([
                        'name_rel',
                        'form_rel' => fn($q) => $q->where('type', $ricetype)->orderBy('id', "ASC")
                    ])
                    ->whereNotNull('min_price')
                    ->whereNotNull('max_price')
                    // ->where('state', $state)
                    ->where('cropYear' , $cropYear)
                    ->where(function ($q) {
                            $q->whereNull('closing')->orWhere('closing', '');
                        })
                    ->whereDate('created_at','<',$lastAvaialbleRecordDate);
            }

            $data = $livePrice->get();

            $riceNameForm = [];
            $states = [];
            $newDataProcess = [];


            if( $cropYear == 2023 ){
                foreach ($data as $k => $val) {
                    if (!str_contains(strtolower($val['form_rel']['form_name']), 'new') && $cropYear == "2023") {
                        $riceNameForm[] = $val->name.'_'.$val->form;
                        $states[] = $val['state'];
                    }
                }
            }else{
                foreach($data as $k => $v){
                    $riceNameForm[] = $v->name.'_'.$v->form;
                    $states[] =  $v->state ;
                }
            }



            
            // dd($);
            dd(array_unique($states));




dd("kjnik");
        $ricename = RiceName::select('id')->where('type', 'non-basmati')->pluck('id')->toArray();


        $ricename = RiceName::where('type', 'basmati')->pluck('id')->toArray();

        
        

        $livePrice = LivePrice::whereNotNull('state_order')
            ->whereNotNull('min_price')
            ->whereNotNull('max_price')
            ->whereIn('name', $ricename)
            ->orderBy('state_order', 'ASC');

        if ($request->has('year')) {

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

        $states = $livePrice->distinct()->pluck('state');

        return response()->json(['error' => null, 'data' => $states], 200);
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
        User::where(['id' => $request->id,'userType' => 1])->update(['userToken' => $request->userToken]);
        return response()->json(['error' => null, 'message' => "Token updated successfully..."]);
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
        if ($request != null) {
            if ($request->has('id')) {
                if ($request->id != '' && $request->id != 'undefined') {
                    $userModel = User::find($request->id);
                    $userModel->user_token = $request->user_token;
                    if ($userModel->save()) {
                        return response()->json(['status' => 'success', 'message' => "Token updated successfully.."], 200);
                    }
                    return response()->json(['status' => 'error', 'message' => "Failed"], 403);
                }
            }
        }
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

    public function checkUserExpired($userId)
    {
        $isExpiry = false;
        $user = User::where('id', $userId)->where('userType' , 1)->first();
        $today = Carbon::now();
        $todayDate = $today->format('Y-m-d');
        if ($user != null) {
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
        return response()->json(['status' => true, 'data' => $user->expired_on, 'isExpiry' => $isExpiry]);
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
        $latestDate = $getUSDPrices->created_at->format('d-m-Y | H:i A');


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
        $latestDate = $getUSDPrices->created_at->format('d-m-Y | H:i A');

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


        $latestDate = $getUSDPrices->created_at->format('d-m-Y | H:i A');

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
        $riceQualityMaster = QualityMaster::select(["id","quality","quality_name","quality_type","quality_type_status","status","order"])->get()->groupBy('quality_type');
        $qualityMaster = RiceName::get()->groupBy('type');
        $riceQualityArray = [];
        $riceQualityDataArray = $qualityMaster->toArray();
        if ($qualityMaster->count()) {
            $riceQualityArray = array_keys($qualityMaster->toArray());
        }

        $usdDefaultMaster = USD_defaultmaster::get()->groupBy('applied_for')->toArray();
        $usdDefaultMasterArray = [];
        foreach ($usdDefaultMaster as $k => $v) {
            if ($k == 1) {
                $usdDefaultMasterArray['basmati'] = $v;
            } else {
                $usdDefaultMasterArray['non-basmati'] = $v;
            }
        }

        $portObject = OceanFreight::select('id', 'region', 'country', 'port', 'freight_25MT')->orderBy('port', 'ASC')->where('port', '!=', '')->get();
        $portArray = $portObject->toArray();
        $data = ['status' => true, 'riceQualityMasterArray' => $riceQualityMaster->toArray(), 'riceQualityType' => $riceQualityArray, 'riceQualityData' => $usdDefaultMasterArray, 'ports' => $portArray, 'riceQualityDataArray' => $riceQualityDataArray];

        return response()->json($data);
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
        $listNotifications = Notification::where('user_id', $userId)->where('status', 0)->get();
        return response()->json(['status' => true, 'data' => $listNotifications->count()], 200);
    }

    public function clearNotifications($userId)
    {
        Notification::where('user_id', $userId)->update(['status' => 1]);
        return response()->json(['status' => true, 'data' => []], 200);
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
        User::where('id', $userId)->update(['status' => 0]);
        return response()->json(['status' => true, 'data' => []], 200);
    }

    public function getBrandList()
    {
        $brands = Brand::orderBy('name')->with(['getAttachments'])->where('status' , 1)->get();
        return response()->json(['sttaus' => true, 'data' => $brands], 200);
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

    public function getRiceWand($riceNameId)
    {
        $wand = WandModel::select(["id","RiceNameId","wandTypeId","value","order","status"])->where('RiceNameId', $riceNameId)->with(['getWandType' => function($q){
            return $q->select(["id","type","order","status"]);
        }])->orderBy('order', 'ASC')->get();
        return response()->json(['status' => true, 'data' => $wand]);
    }

    public function getSellerPackingINR()
    {
        $sellerPackingINR = SellerPackingINR::get();
        return response()->json(['status' => true, 'data' => $sellerPackingINR]);
    }


    public function FutureSubmitSellQuery(Request $request)
    {
        $data = [];

        $selectedQualityTypeInt = $request->selectedQualityTypeInt;
        $year = $request->crop_year?? '2023';
        $quality = $request->quality;
        $qualityForm = $request->qualityForm;
        $selectedGrade = $request->selectedGrade;
        $changePackingType = $request->changePackingType;
        $quantity = $request->quantity;
        $offerPrice = $request->offerPrice;
        $validDays = $request->validDays;
        $contactperson = $request->contactPerson;
        $contactMobile = $request->contactMobile;
        $userId = $request->user_id;
        $farming = $request->farming;
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

        $data['farming'] = $farming;
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

        $data = array();

        $mailTo = "enquiry@sntcgroup.com";
        $mailMessage = '';
        $subject = 'Future Sell with SNTC';
        $mailFrom = 'info@sntcgroup.com';
        $mailFromName = 'SNTC Team - India';

        $respose = Mail::send('mail.FutureSellQueryReceivedMilestone3', $data, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
            $message->to($mailTo, $mailMessage)->subject($subject);
            $message->from($mailFrom, $mailFromName);
        });
        return response()->json(['status' => true, 'data' => $sellCreate]);





    }


    public function FutureSubmitBuyQuery(Request $request)
    {
        $data = [];
        
        $farming = $request->farming ?? '';
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
       

        $data['farming'] = $farming;
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
        $data = array();

        $mailTo = "enquiry@sntcgroup.com";
        $mailMessage = '';
        $subject = 'Future Buy with SNTC - Web Version';
        $mailFrom = 'info@sntcgroup.com';
        $mailFromName = 'SNTC Team - India';

        $respose = Mail::send('mail.FutureBuyqueryReceivedMilestone3', ['contactPerson' => $contactPerson, 'contactMobile' => $contactMobile], function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
            $message->to($mailTo, $mailMessage)->subject($subject);
            $message->from($mailFrom, $mailFromName);
        });
        return response()->json(['status' => true, 'data' => $buyerQuery]);
    }


    public function SubmitSellQuery(Request $request)
    {
        $data = [];

        $selectedQualityTypeInt = $request->selectedQualityTypeInt;
        $quality = $request->quality;
        $qualityForm = $request->qualityForm;
        $selectedGrade = $request->selectedGrade;
        $changePackingType = $request->changePackingType;
        $quantity = $request->quantity;
        $offerPrice = $request->offerPrice;
        $validDays = $request->validDays;
        $contactperson = $request->contactperson;
        $contactMobile = $request->contactMobile;
        $warehouselocation = $request->warehouselocation;
        $userId = $request->userId;
        $farming = $request->farming;
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
        $data['farming'] = $farming;
        $data['type'] = $type;
        $data['riceSize'] = $riceSize;


        $sellCreate = SellQueriesINR::create($data);

        $data = array();

        $mailTo = "enquiry@sntcgroup.com";
        $mailMessage = '';
        $subject = 'Sell with SNTC';
        $mailFrom = 'info@sntcgroup.com';
        $mailFromName = 'SNTC Team - India';

        $respose = Mail::send('mail.SellQueryReceivedMilestone3', $data, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
            $message->to($mailTo, $mailMessage)->subject($subject);
            $message->from($mailFrom, $mailFromName);
        });
        return response()->json(['status' => true, 'data' => $sellCreate]);
    }



    public function SubmitSellQueryWeb(Request $request)
    {
        $data = [];

        $selectedQualityTypeInt = $request->selectedQualityTypeInt;
        $quality = $request->quality;
        $qualityForm = $request->qualityForm;
        $selectedGrade = $request->selectedGrade;
        $changePackingType = $request->changePackingType;
        $quantity = $request->quantity;
        $offerPrice = $request->offerPrice;
        $validDays = $request->validDays;
        $contactperson = $request->contactperson;
        $contactMobile = $request->contactMobile;
        $warehouselocation = $request->warehouselocation;
        $userId = $request->userId;
        $farming = $request->farming;
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
        $data['farming'] = $farming;
        $data['type'] = $type;
        $data['riceSize'] = $riceSize;


        $sellCreate = SellQueriesINR::create($data);

        $data = array();

        $mailTo = "enquiry@sntcgroup.com";
        $mailMessage = '';
        $subject = 'Sell with SNTC - Web version';
        $mailFrom = 'info@sntcgroup.com';
        $mailFromName = 'SNTC Team - India';

        $respose = Mail::send('mail.SellQueryReceivedMilestone3', $data, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
            $message->to($mailTo, $mailMessage)->subject($subject);
            $message->from($mailFrom, $mailFromName);
        });
        return response()->json(['status' => true, 'data' => $sellCreate]);
    }



    public function getTrade($userId)
    {
        // $trade = TradeQueriesINR::with(['TradeInterest','TradeLikeAll' => function($query) use($userId){
        //     return $query->where('userId' , $userId);
        // },'RiceFormMilestone3','RiceQualityMaster','riceGrade' => function($query){
        //     return $query->with('getWandType')->get();
        // },'RicePacking'])->withCount('TradeLikeAll')->get()->groupBy('tradeType');

        $now = Carbon::now();
        $date = Carbon::parse($now)->toDateString();
        $time = Carbon::parse($now)->format('H:i');

        // dd(Carbon::parse($now)->format('Y-m-d H:i'));
        // dd($time);
        // dd(TradeQueriesINR::whereIn('status' , [1,6,4,5,11,12])->where('validDays' ,'<=', Carbon::parse($now)->format('Y-m-d H:i'))->get());

        TradeQueriesINR::whereIn('status', [1, 6, 4, 5, 12])->where('validDays', '<=', Carbon::parse($now)->format('Y-m-d H:i'))->update(['status' => 2]);

        // $todayExpired = TradeQueriesINR::orderBy('status' , 'ASC')->where('status' , 2)->get();
        $allTrade = TradeQueriesINR::where('status', '!=', 2)
        // ->limit(75)
        ->orderByRaw('FIELD(status,6,4,3)')->with(['TradeInterest' => function ($query) use ($userId) {
            return $query->where('userId', $userId)->get();
        }, 'RiceNameData', 'TradeLikeAll' => function ($query) use ($userId) {
            return $query->select(['id' ,'tradeId'])->where('userId', $userId);
        }, 'RiceFormMilestone3', 'riceGrade' => function ($query) {
            return $query->with('getWandType')->get();
        }, 'RicePackingBuyer', 'RicePackingSeller'])->where('status', '!=', 5)->orderBy('id' , 'DESC')->withCount('TradeLikeAll')->get();

        // $allTrade = TradeQueriesINR::orderBy('status' , 'ASC')->limit(75)->orderBy('id' , 'DESC')->with(['TradeInterest'=> function($query) use($userId){
        //     return $query->where('userId' , $userId)->get();
        // },'RiceNameData','TradeLikeAll' => function($query) use($userId){
        //     return $query->where('userId' , $userId);
        // },'RiceFormMilestone3','riceGrade' => function($query){
        //     return $query->with('getWandType')->get();
        // },'RicePackingBuyer','RicePackingSeller'])->where('status' ,'!=',5)->withCount('TradeLikeAll')->get();

        $trade = $allTrade->groupBy('tradeType');


        $tradeStatus = TradeCurrentStatus::first();

        return response()->json(['status' => true, 'data' => $trade, 'allTrade' => $allTrade, 'currentStatus' => $tradeStatus['currentStatus'], 'statusMessage' => $tradeStatus['message']]);
    }

    public function getWebTrades($userId)
    {
        $now = Carbon::now();
        $date = Carbon::parse($now)->toDateString();
        $time = Carbon::parse($now)->format('H:i');

        TradeQueriesINR::whereIn('status', [1, 6, 4, 5,11, 12])->where('validDays', '<=', Carbon::parse($now)->format('Y-m-d H:i'))->update(['status' => 2]);

        $allTrade = TradeQueriesINR::where('status', '!=', 2)
        ->limit(75)
        ->orderByRaw('FIELD(status,6,4,12,11,3)')->with(['TradeInterest' => function ($query) use ($userId) {
            return $query->where('userId', $userId)->get();
        }, 'RiceNameData', 'TradeLikeAll' => function ($query) use ($userId) {
            return $query->select(['id' ,'tradeId'])->where('userId', $userId);
        }, 'RiceFormMilestone3', 'riceGrade' => function ($query) {
            return $query->with('getWandType')->get();
        }, 'RicePackingBuyer', 'RicePackingSeller'])
        ->where('status', '!=', 5)

        // ->where(function($q){
        //     $twoDaysAgo = Carbon::now()->subDays(30)->toDateString();
        //     $q->where('status', '!=', 3)
        //       ->orWhere(function($qq) use ($twoDaysAgo){
        //           $qq->where('status', 3)
        //              ->whereDate('created_at', '>=', $twoDaysAgo);
        //       });
        // })
        ->orderBy('id' , 'DESC')->withCount('TradeLikeAll')->get();

        $trade = $allTrade;
        // $trade = $allTrade->groupBy('tradeType');
        $tradeStatus = TradeCurrentStatus::first();

        return response()->json(['status' => true, 'data' => $trade, 'currentStatus' => $tradeStatus['currentStatus'], 'statusMessage' => $tradeStatus['message']]);
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

        $SellQueriesINR = $this->applyPackingLogic(
            SellQueriesINR::where('created_by', $userId)
                ->selectRaw("
                    sell_query_milestone3.*,
                    contactperson AS contactPerson
                ")
                // ->selectRaw("
                //     sell_query_milestone3.*,
                //     contactperson AS contactPerson,
                //     // CASE 
                //     //     WHEN quality_type = 1 THEN 2
                //     //     WHEN quality_type = 0 THEN 1
                //     //     ELSE quality_type
                //     // END AS quality_type
                // ")
                ->with([
                    'RiceFormMilestone3:id,name',
                    'RiceQualityRiceNames:id,name',
                    'riceGrade:id,value,wandTypeId',
                    'RicePacking:id,packing,description'
                ])
                ->get()
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

        $FutureSellQueriesINR = $this->applyPackingLogic(
            FutureSellQueriesINR::where('created_by', $userId)
             ->selectRaw("
                    future_sell_query_milestone3.*,
                    contactperson AS contactPerson
                ")
              // ->selectRaw("
              //       future_sell_query_milestone3.*,
              //       contactperson AS contactPerson,
              //       // CASE 
              //       //     WHEN quality_type = 1 THEN 2
              //       //     WHEN quality_type = 0 THEN 1
              //       //     ELSE quality_type
              //       // END AS quality_type
              //   ")
                ->with([
                    'RiceQualityRiceNames:id,name',
                    'riceGrade:id,value',
                    'RicePacking:id,packing,description',
                    'UserDetail:id,name',
                    'RiceFormMilestone3:id,name'
                ])
                ->get()
        );
        return response()->json([
            'status' => true, 
            'data' => [ 'buy' =>  $BuyQueriesINR, 'sell' => $SellQueriesINR , 'futureBuy' => $FutureBuyQueriesINR , 'futureSell' => $FutureSellQueriesINR ]
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
    public function getPersonalTrade($userId)
    {
        $BuyQueriesINR = BuyQueriesINR::where('created_by' , $userId)->pluck('id')->toArray();
        $SellQueriesINR = SellQueriesINR::where('created_by' , $userId)->pluck('id')->toArray();
        $FutureBuyQueriesINR = FutureBuyQueriesINR::where('created_by' , $userId)->pluck('id')->toArray();
        $FutureSellQueriesINR = FutureSellQueriesINR::where('created_by' , $userId)->pluck('id')->toArray();
        
        $tradePre = TradeQueriesINR::whereNotNull('queryId');

        // 1= buy , 2 = sell , 3 = futureBuy , 4 = futureSell
        $BuyQuery = (clone $tradePre)->where('tradeFor' , 1)->whereIn('queryId' , $BuyQueriesINR)->get();
        $SellQuery = (clone $tradePre)->where('tradeFor' , 2)->whereIn('queryId' , $SellQueriesINR)->get();
        $FutureBuyQuery = (clone $tradePre)->where('tradeFor' , 3)->whereIn('queryId' , $FutureBuyQueriesINR)->get();
        $FutureSellQuery = (clone $tradePre)->where('tradeFor' , 4)->whereIn('queryId' , $FutureSellQueriesINR)->get();

        return response()->json(['status' => true, 'data' => ['BuyQuery' => $BuyQuery , 'SellQuery' => $SellQuery , 'FutureBuyQuery' => $FutureBuyQuery , 'FutureSellQuery' => $FutureSellQuery]]);
    }

    public function getTradeCounts(Request $request)
    {

        $now = Carbon::now();
        $date = Carbon::parse($now)->toDateString();
        $time = Carbon::parse($now)->format('H:i');

        TradeQueriesINR::whereIn('status', [1, 6, 4, 5, 11, 12])->where('validDays', '<=', Carbon::parse($now)->format('Y-m-d H:i'))->update(['status' => 2]);

        $allTrade = TradeQueriesINR::where('status', 1)
            ->where(function($query) use ($request) {
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
                    // $query->where('qualityForm', $request->quality_form);
                }
                if ($request->has('rice_size')) {
                    $query->where('riceSize', $request->rice_size);
                }
                // Add more filters as needed
            })
            ->limit(75)
            ->orderByRaw('FIELD(status,6,4,3)')
            ->orderBy('id', 'DESC')
            ->get();

        $trade = $allTrade->groupBy('tradeType');
        $tradeStatus = TradeCurrentStatus::first();

        // return response()->json(['status' => true, 'data' => $trade, 'allTrade' => $allTrade, 'currentStatus' => $tradeStatus['currentStatus'], 'statusMessage' => $tradeStatus['message']]);


        // 3 => "sold", 2 => 'expired' , 1 => 'Pending',6=>'Active',4=>'In-Process',5=>'De-active',11 => 'close', 12=> 'hold'   
        // $tradePre = TradeQueriesINR::whereIn("status", [1,4]);

        $BuyQuery = (clone $allTrade)->where('tradeType' , 1)->count();
        $SellQuery = (clone $allTrade)->where('tradeType' , 2)->count();
        $FutureBuyQuery = (clone $allTrade)->where('tradeType' , 3)->count();
        $FutureSellQuery = (clone $allTrade)->where('tradeType' , 4)->count();

        return response()->json(['status' => true, 'data' => ['BuyQuery' => $BuyQuery , 'SellQuery' => $SellQuery , 'FutureBuyQuery' => $FutureBuyQuery , 'FutureSellQuery' => $FutureSellQuery]]);
    }

    public function getPersonalQueryCount($userId)
    {
        $BuyQueriesINR = BuyQueriesINR::where('created_by' , $userId)->pluck('id')->toArray();
        $SellQueriesINR = SellQueriesINR::where('created_by' , $userId)->pluck('id')->toArray();
        $FutureBuyQueriesINR = FutureBuyQueriesINR::where('created_by' , $userId)->pluck('id')->toArray();
        $FutureSellQueriesINR = FutureSellQueriesINR::where('created_by' , $userId)->pluck('id')->toArray();
        
        $availableQueries = array_merge($BuyQueriesINR,$SellQueriesINR,$FutureBuyQueriesINR,$FutureSellQueriesINR);
        $tradePre = TradeQueriesINR::whereNotNull('queryId')->whereIn('queryId' , $availableQueries);

        $tradePreCount = (clone $tradePre)->count();
        $tradePreSoldCount = (clone $tradePre)->where('status' ,3)->count();

        return response()->json(['status' => true, 'data' => ['availableQueries' => count($availableQueries) , 'trades' => $tradePreCount , 'soldCount' => $tradePreSoldCount    ]]);
    }

    public function filterTrade(Request $request , $userId)
    {
        
        $now = Carbon::now();
        $date = Carbon::parse($now)->toDateString();
        $time = Carbon::parse($now)->format('H:i');

        TradeQueriesINR::whereIn('status', [1, 6, 4, 5, 11, 12])->where('validDays', '<=', Carbon::parse($now)->format('Y-m-d H:i'))->update(['status' => 2]);

        $allTrade = TradeQueriesINR::where('status', '!=', 2)
            ->where('status', '!=', 5)
            ->where(function($query) use ($request) {
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
                    // $query->where('qualityForm', $request->quality_form);
                }
                if ($request->has('rice_size')) {
                    $query->where('riceSize', $request->rice_size);
                }
                // Add more filters as needed
            })
            // ->limit(75)
            ->orderByRaw('FIELD(status,6,4,3)')
            ->with([
                'TradeInterest' => function ($query) use ($userId) {
                    $query->where('userId', $userId);
                },
                'RiceNameData',
                'TradeLikeAll' => function ($query) use ($userId) {
                    $query->where('userId', $userId);
                },
                'RiceFormMilestone3',
                'riceGrade' => function ($query) {
                    $query->with('getWandType');
                },
                'RicePackingBuyer',
                'RicePackingSeller'
            ])
            ->orderBy('id', 'DESC')
            ->withCount('TradeLikeAll')->get();

        $trade = $allTrade->groupBy('tradeType');

        $tradeStatus = TradeCurrentStatus::first();

        return response()->json(['status' => true, 'data' => $trade, 'allTrade' => $allTrade, 'currentStatus' => $tradeStatus['currentStatus'], 'statusMessage' => $tradeStatus['message']]);
    }

    public function webFilterTrade(Request $request , $userId)
    {
        $now = Carbon::now();
        $date = Carbon::parse($now)->toDateString();
        $time = Carbon::parse($now)->format('H:i');

        TradeQueriesINR::whereIn('status', [1, 6, 4, 5, 11, 12])->where('validDays', '<=', Carbon::parse($now)->format('Y-m-d H:i'))->update(['status' => 2]);

        $allTrade = TradeQueriesINR::whereNotIn('status',[2,5])
            ->where(function($query) use ($request) {
                if ($request->has('trade_type')) {
                    $query->where('tradeType', $request->trade_type);
                }else{
                    $query->whereIn('tradeType', [1,2,3,4]);
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
                    if( $request->has('state') ){
                        $query->where('qualityFormLinkWithLivePrice', $request->quality_form);
                    }else{
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

                

                // if( $request->has('packing') ) {
                //     $query->where('packing' , $request->packing);
                // }
                // Add more filters as needed
            })
            // ->limit(75)
            ->orderByRaw('FIELD(status,6,4,12,11,3)')
            ->with([
                'TradeInterest' => function ($query) use ($userId) {
                    $query->where('userId', $userId);
                },
                'RiceNameData',
                'TradeLikeAll' => function ($query) use ($userId) {
                    $query->where('userId', $userId);
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
            ->withCount('TradeLikeAll')->get();

        $trade = $allTrade;
        // $trade = $allTrade->groupBy('tradeType');

        $tradeStatus = TradeCurrentStatus::first();

        return response()->json(['status' => true, 'data' => $trade, 'allTrade' => $allTrade, 'currentStatus' => $tradeStatus['currentStatus'], 'statusMessage' => $tradeStatus['message']]);
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
        $data = [];

        $selectedQualityTypeInt = $request->selectedQualityTypeInt;
        $quality = $request->quality;
        $qualityForm = $request->qualityForm;
        $selectedGrade = $request->selectedGrade;
        $changePackingType = $request->changePackingType;
        $packing = $request->packing;
        $quantity = $request->quantity;
        $additionalinfo = $request->additionalinfo;
        $userId = $request->user_id;
        $farming = $request->farming ?? '';
        $contactPerson = $request->contactPerson ?? '';
        $contactMobile = $request->contactMobile ?? '';
        $type = $request->type ?? 'app';

        $data['farming'] = $farming;
        $data['contactPerson'] = $contactPerson;
        $data['contactMobile'] = $contactMobile;
        $data['type'] = $type;

        $data['quality_type'] = $selectedQualityTypeInt;
        $data['quality'] = $quality;
        $data['quality_form'] = $qualityForm;
        $data['grade'] = $selectedGrade;
        $data['packing_type'] = $changePackingType;
        $data['packing'] = $packing;
        $data['quantity'] = $quantity;
        $data['additional_info'] = $additionalinfo;
        $data['contactPerson'] = $contactPerson;
        $data['contactMobile'] = $contactMobile;
        $data['created_by'] = $userId;


        $buyerQuery = BuyQueriesINR::create($data);
        $viewData = [
            'contactPerson'  => $contactPerson,
            'contactMobile'  => $contactMobile
            // add everything you need
        ];
        // $data = array();

        // $mailTo = "enquiry@sntcgroup.com";
        $mailTo = "sandy.singh51480@gmail.com";
        $mailMessage = '';
        $subject = 'Buy with SNTC - Web Version' ;
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
        $subject = 'Notification of trade interested SNTC -- Web Version';
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
        $brandAvail = BrandAvailability::where([ 'brand_id' => $brandId ])->with(['state_rel' , 'city_rel'])->get();
        return response()->json([ 'status' => true , 'message' => 'Brand Availability get successfully' , 'data' => $brandAvail ] , 200);
    }

    public function adminIsViewedByAdmin()
    {
        $hasUnattendedUser = User::where(['user_from' => 'web', 'is_viewed_by_admin' => 0])->count();
        return response()->json([ 'status' => true , 'message' => 'count get successfully' , 'count' => $hasUnattendedUser ] , 200);
    }
}
