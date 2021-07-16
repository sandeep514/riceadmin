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
    use App\Gallery;
    use App\RiceName;
    use App\RiceType;
    use App\RiceForm;
    use App\Order;
    use App\Plan;
    use App\SubPlan;
    use App\Message;
    use App\TrialPeriod;
    use App\Helpers\StatusChat;
// use Illuminate\Support\Facades\Hash;
    use Carbon\Carbon;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Hash;
    use App\FreeTrialMonths;
    use App\Http\Controllers\MailController;

    class ApiController extends Controller
    {
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
        
        public function getChatStatus(){
            return ['status' => 'success' , 'data' => StatusChat::getStatus()];
        }
        
        public static function sendGCM($message)
        {
            
            $url = 'https://fcm.googleapis.com/fcm/send';
            $fields = [
                'registration_ids' => [
                    'cdtlbIZUReSKl4xkn0SfKr:APA91bEezbuCTnLh4E3DxulE_8zYDwJLijd3ksGkdUtV0JFxU_il3Fdim_7FbTfpu1oM0EYdrS2oB05BGZgz6GhnrW8R1i7LEwKffEbFGpxPaNrSR5LHQ23LWKFcsN789FMmzscRyJRH'
                    ],
                'data' => [ "message" => $message]
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
            $userModel = User::where(['email' => $request->email])->first();
            if( $userModel == null ){
            	$userModel = User::where(['mobile' => $request->email])->first();
            }

            if ($userModel == null) {
                return response()->json(['status' => 'error', 'message' => 'Wrong user detail']);
            }
            $oldPassword = $userModel->password;
            if (Hash::check($request->password, $oldPassword)) {
                
                if ($userModel->status == 0) {
                    $Newotp = $userModel->otp;
                    $mobile = $userModel->mobile;
					file_get_contents('http://anysms.in/api.php?username=rijulbajaj&password=662564&sender=SNTCGR&sendto='.$mobile.'&message=Thank+you+for+registering+on+SNTC+Rice+Live+Pricing+App.+Your+OTP+Code+is+'.$Newotp.'&PEID=1701160336234687231&templateid=1707161795904090251');

                    if($userModel->email != null){
                        $response = MailController::generateMailForOTPThanks($userModel->email,'no@replay.in','SNTC GROUP','Thank you for registering on SNTC Rice Live Pricing App.','Thank you for registering on SNTC Rice Live Pricing App.',$Newotp);
                    }

                    return response()->json(['status' => 'success', 'user' => $userModel]);
                }
                return response()->json(['status' => 'success', 'user' => $userModel]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Wrong user detail']);
            }
        }
        
        public function sendOTP($number,$isOTP = false)
        {
            $otp = rand(1111, 9999);
            $user = User::where('mobile', $number)->first();
            User::where('mobile', $number)->update(['otp' => $otp]);

            $message = "Dear Customer, Your SNTC live pricing premium membership is now active, we are so excited to unlock PREMIUM benefits for you , Enjoy free live prices for the all the rice products. TCA.";

            if($isOTP == true){
                file_get_contents('http://anysms.in/api.php?username=rijulbajaj&password=662564&sender=SNTCGR&sendto=' . $number . '&message=' . urlencode($message));
                if($user->email != null){
                    $response = MailController::generateMail($user->email,'no@replay.in','SNTC GROUP',$message,'SNTC Live Pricing Premium Membership '); 
                }
            }

            if ($isOTP == false) {
				file_get_contents('http://anysms.in/api.php?username=rijulbajaj&password=662564&sender=SNTCGR&sendto=' . $number . '&message=Your+forgot+password+OTP+for+SNTC+Rice+Live+Pricing+App+is+' . $otp.'&PEID=1701160336234687231&templateid=1707161848973558040');
                User::where('mobile', $number)->update(['otp' => $otp]);
                if($user->email != null){
                   $response = MailController::generateMailForOTP($user->email,'no@replay.in','SNTC GROUP',null,'SNTC OTP Verification ',$otp);
                }
            }
            
            return response()->json(['error' => null, 'data' => $otp,'mailResponse' => $response], 200);
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
        
        public function getPrices($state, $ricetype)
        {
            $replacehiphen = explode('-', $ricetype);
            $replaceWithUnderscore = implode('_', $replacehiphen);
            
            $processedData = [];
            $lastRecord = LivePrice::where('name' ,'!=', '0')->where('form' , '!=' , '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('id' , 'DESC')->first();

            if ($lastRecord != null) {
            
                $prices = LivePrice::where('name' ,'!=', '0')->where('form' , '!=' , '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->with(['name_rel' => function($query) use($ricetype){
                    return $query->where('type' , $ricetype)->get();
                },'form_rel' => function ($query) use ($ricetype) {
                        return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
                    }
                ])->where('state' , $state)->whereDate('created_at',$lastRecord->created_at->format('Y-m-d'))->get();
                $lastToLastDate = LivePrice::where('name' ,'!=', '0')->where('form' , '!=' , '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('created_at', 'DESC')
                ->whereDate('created_at', '<',$lastRecord->created_at->format('Y-m-d'))->get();

                if (!$lastToLastDate->isEmpty()) {
                    $pricesprevious = LivePrice::where('min_price', '!=', null)->where('max_price', '!=', null)->with([
                        'name_rel' => function($query){
                            return $query->get();
                        }, 'form_rel' => function ($query) use ($ricetype) {
                            return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
                        }
                    ])->where(['state' => $state])->where(DB::raw('date(created_at)'),
                        $lastToLastDate[0]->created_at->format('Y-m-d'))->get();
                    
                    $data = LivePrice::where('min_price', '!=', null)->where('max_price', '!=', null)->with([
                        'name_rel' => function($query){
                            // return $query->orderBy('order', 'asc')->get();
                            return $query->get();
                        },'form_rel' => function ($query) use ($ricetype) {
                            return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
                        }
                    ])->where(['state' => $state])->where(DB::raw('date(created_at)'),
                        $lastRecord->created_at->format('Y-m-d'))->orWhere(DB::raw('date(created_at)'),
                        $lastToLastDate[0]->created_at->format('Y-m-d'))->get();
                    
                    
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

                    foreach($processedData as $k => $v){
                        foreach($v as $kk => $vv){
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
                                }else{
                                    foreach($val as $kk => $vv ){
                                        // dd($processedData[$k][$key][$kk] );
                                        if( $kk != 0 ){
                                          $processedData[$k][$key][$kk]['isHide'] = 'true'; 
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $newProccessedData = [];
                    
                    $newData = collect($processedData)->map(function($item){
                        return collect($item)->map(function($innerItem) use ($item){
                            $onlyValues = array_values($innerItem);
                            $onlyKeys = array_keys($innerItem);
                            foreach($onlyValues as $k => $v){
                                if( $k == 0 ){
                                    $onlyValues[$k]['is_hide'] = 'false';        
                                }else{
                                    $onlyValues[$k]['is_hide'] = 'true';
                                }
                            }
                            
                            
                            $data = array_combine( $onlyKeys, $onlyValues);
                            return $data;
                        });
                    })->toArray();
                    
                    $order = [];
                    foreach($newData as $k => $v){
                        foreach($v as $kk => $vv){
                            $order[$k][] = [ $kk => $vv] ;
                        }
                    }
                    
                    $myNewData = [];
                    foreach($order as $k => $v){
                        foreach($v as $kk => $vv){
                            $newDataProcess = [];
                            foreach($vv as $key => $value){
                                foreach($value as $ke => $val){
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
                    'latest' => $lastRecord->created_at->format('Y-m-d'),
                    'oldDate' => ''
                ]);
            } else {
                print_r('kjhnjki');
                die();
                $data = LivePrice::where('state' , $state)->where('min_price', '!=', null)->where('max_price', '!=', null)->with([
                    'name_rel',
                    'form_rel' => function ($query) use ($ricetype) {
                        return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
                    }
                ])->where(['state' => $state])->where(DB::raw('date(created_at)'),
                    Carbon::now()->format('Y-m-d'))->get();
                
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
                    'latest' => '',
                    'oldDate' => ''
                ]);
            }
            
        }
        
        
        public function getPorts()
        {
            $lastUpdatedDate = Port::orderBy('created_at' , 'DESC')->first();

            $dateCreate = date_create($lastUpdatedDate->created_at);
            $formatedDate = date_format($dateCreate , 'Y/m/d');
            $listPort = Port::whereDate('created_at' , $formatedDate)->orderBy('id' , 'DESC')->where('route' ,'!=', 0)->get()->groupBy('state');
            
            return response()->json(['errors' => null, 'list' => $listPort]);
        }
        
        public function getpriceByTimePeriod($state, $riceType, $rice, $timePeriod)
        {
            $state = base64_decode($state);
            $riceType = base64_decode($riceType);
            $rice = base64_decode($rice);
            $timePeriod = base64_decode($timePeriod);
        
            $todayDate = Carbon::now();
            $created_at = [];
            $min_price = [];
            $max_price = [];
            
            $productType = RiceName::select('type')->where('name', $rice)->first();

            $riceName = RiceName::select('id')->where('name', $rice)->first();
            $explodeRiceType = explode('_', $riceType);
            $implodeRiceType = implode(' ', $explodeRiceType);
            $type = RiceForm::select('id')->where('form_name', $implodeRiceType)->where('type' , $productType->type)->first();

            $fromDate = $todayDate->format('y-m-d');


            $prices = LivePrice::where('name', $riceName->id)->where('form', $type->id)->with([
                'name_rel','form_rel' => function ($query) use ($riceType) {
                    return $query->where('type', $riceType)->get();
                }
            ])->where(['state' => $state])->where(DB::raw('date(created_at)'), '<=', $fromDate)->get();

            foreach ($prices as $k => $v) {
                $created_at[] = strtotime($v->created_at->format('y-m-d'));
            }
            foreach ($prices as $key => $value) {
                $max_price[] = $value->max_price;
            }
            
            $combine = array_combine($created_at , $max_price);
            $combinedData = [];
            foreach($combine as $kk => $vv){
                $combinedData[] = [$kk*1000 , (int)$vv];
            }
            $responseData = ['errors' => null, 'date' => $created_at, 'prices' => $max_price , 'combinedData' => $combinedData,'productType' => $productType];
            return response()->json($responseData);
            


            if ($timePeriod == '15_Days') {
                $fromDate = $todayDate->subDays(15)->format('y-m-d');
                $prices = LivePrice::where('name', $riceName->id)->where('form', $type->id)->with([
                    'name_rel','form_rel' => function ($query) use ($riceType) {
                        return $query->where('type', $riceType)->get();
                    }
                ])->where(['state' => $state])->where(DB::raw('date(created_at)'), '>', $fromDate)->get();
                
                foreach ($prices as $k => $v) {
                    $created_at[] = $v->created_at->format('y-m-d');
                }
                foreach ($prices as $key => $value) {
                    $max_price[] = $value->max_price;
                }
            }
            
            if ($timePeriod == '1_Month') {
                $fromDate = $todayDate->subDays(30)->format('y-m-d');
                
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
            }
        
            if ($timePeriod == '2_Month') {
                $fromDate = $todayDate->subDays(60)->format('y-m-d');
                
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
            }
            
            if ($timePeriod == '3_Month') {
                $fromDate = $todayDate->subDays(90)->format('y-m-d');
                
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
            }
            
            if ($timePeriod == '4_Month') {
                $fromDate = $todayDate->subDays(120)->format('y-m-d');
                
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
            }
            
            if ($timePeriod == '5_Month') {
                $fromDate = $todayDate->subDays(150)->format('y-m-d');
                
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
            }
            
            if ($timePeriod == '6_Month') {
                $fromDate = $todayDate->subDays(180)->format('y-m-d');
                
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
            }
            
            if ($timePeriod == '7_Month') {
                $fromDate = $todayDate->subDays(210)->format('y-m-d');
                
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
            }
            
            if ($timePeriod == '8_Month') {
                $fromDate = $todayDate->subDays(240)->format('y-m-d');
                
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
            }
            
            if ($timePeriod == '9_Month') {
                $fromDate = $todayDate->subDays(270)->format('y-m-d');
                
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
            }
            
            if ($timePeriod == '10_Month') {
                $fromDate = $todayDate->subDays(300)->format('y-m-d');
                
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
            }
            
            if ($timePeriod == '11_Month') {
                $fromDate = $todayDate->subDays(330)->format('y-m-d');
                
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
            }
            
            if ($timePeriod == '12_Month') {
                $fromDate = $todayDate->subDays(360)->format('y-m-d');
                
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
            if( $trialPeriod ){
                $trialPeriodMonth = $trialPeriod->month;
                $month = $newExpiryDate->month;
                $expiredDate = Carbon::now()->addMonth($month)->format('Y-m-d');
            }

            $data = [
                'buyer' => 5,
                'supplier' => 6,
                'broker' => 7,
                'guest' => 8
            ];

            $hasEmail = User::where(['email' => $request->email])->get();
            if ($hasEmail->count() > 0) {
                return response()->json(['error' => 'Email already exist.', 'data' => []], 500);
            }
            
            $hasMobile = User::where(['mobile' => $request->mobile])->get();
            if ($hasMobile->count() > 0) {
                return response()->json(['error' => 'Mobile Number already exist.', 'data' => []], 500);
            }
            
            $otp = rand(1111, 9999);
            $user = User::create([
                'name' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mobile' => $request->mobile,
                'companyname' => $request->companyname,
                'role' => $data[$request->userState],
                'otp' => $otp,
                'expired_on' => $expiredDate,
                'status' => 0
            ]);
            
            User::where('mobile', $request->mobile)->update(['otp' => $otp]);
            file_get_contents('http://anysms.in/api.php?username=rijulbajaj&password=662564&sender=SNTCGR&sendto='.$request->mobile.'&message=Thank+you+for+registering+on+SNTC+Rice+Live+Pricing+App.+Your+OTP+Code+is+'.$otp.'&PEID=1701160336234687231&templateid=1707161795904090251');
            
            if ($user) {
                if($user->email != null){
                   $response = MailController::generateMailForOTPThanks($user->email,'no@replay.in','SNTC GROUP','Thank you for registering on SNTC Rice Live Pricing App.','Thank you for registering on SNTC Rice Live Pricing App.',$otp);
                }
                return response()->json(['error' => null, 'data' => $user], 200);
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
            $hasEmail = User::where(['email' => $request->email])->where('id' , '!=' , $request->userId )->get();
            if ($hasEmail->count() > 0) {
                return response()->json(['error' => 'Email already exist.', 'data' => []], 500);
            }
            
            $hasMobile = User::where(['mobile' => $request->mobile])->where('id' ,'!=' , $request->userId)->get();
            if ($hasMobile->count() > 0) {
                return response()->json(['error' => 'Mobile Number already exist.', 'data' => []], 500);
            }
            
            $otp = rand(1111, 9999);
            $user = User::where('id' , $request->userId)->update([
                'name' => $request->username,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'companyname' => $request->companyname,
                'role' => $data[$request->userState],
            ]);
            
            $user = User::where('id' , $request->userId)->first();
            if ($user) {
                return response()->json(['error' => null, 'data' => $user], 200);
            } else {
                return response()->json(['error' => "Something went wrong.", 'data' => []], 500);
            }
        }
        
        public function verifyUser(Request $request)
        {
            $userDetails = User::where(['mobile' => $request->mobile, 'otp' => $request->otp])->first();
            
            if ($userDetails != null) {
                User::where(['mobile' => $request->mobile, 'otp' => $request->otp])->update(['status' => 1]);
                // $this->sendOTP($request->mobile,false);
                return response()->json(['error' => "success", 'data' => []], 200);
            } else {
                return response()->json(['error' => "Wrong OTP.", 'data' => []], 500);
            }
        }
        
        public function verifyOTP($number, $otp)
        {
            $user = User::where(['mobile' => $number, 'otp' => $otp])->get();
            if ($user->count() > 0) {
                // $this->sendOTP($user[0]->mobile , true);

                return response()->json(['error' => null, 'data' => $otp], 200);
            } else {
                return response()->json(['error' => null, 'data' => null], 500);
            }
        }
        
        public function changePassword(Request $request)
        {
            $user = User::where(['mobile' => $request->number])->update(['password' => Hash::make($request->password)]);
            if ($user != '') {
                return response()->json(['error' => null, 'data' => null]);
            } else {
                return response()->json(['error' => null, 'data' => null], 500);
            }
        }
        
        public function getBasmatiState()
        {
            $ricename = RiceName::select('id')->where('type', 'basmati')->pluck('id')->toArray();
            $lastRecord = LivePrice::where('name' ,'!=',0)->where('form' , '!=' , 0)->where('min_price', '!=', null)->where('max_price', '!=', null)->get()->last();
            if($lastRecord != null){
                $lastEnteredRecord = $lastRecord->created_at->format('Y-m-d');
                
                $livePrice = LivePrice::whereDate('created_at',$lastEnteredRecord)->where('state_order', '!=', null)->where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('state_order' , 'ASC')->whereIn('name', $ricename)->get()->map(function($query){
                    return $query->state;
                });

                // if( $livePrice->count() == 0 ){
                //     $lastRecord = LivePrice::whereDate('created_at' , '<' , $lastEnteredRecord )->get()->last();
                //     $lastEnteredRecord = $lastRecord->created_at->format('Y-m-d');
                
                //     $livePrice = LivePrice::whereDate('created_at',$lastEnteredRecord)->where('min_price', '!=', null)->where('max_price', '!=', null)->whereIn('name', $ricename)->get()->map(function($query){
                //         return $query->state;
                //     });
                // }

                if( count($livePrice) > 0 ){
                    $livePrice = array_unique($livePrice->toArray());
                    $livePrice = array_values($livePrice);
                }else{
                    $livePrice = [];
                }
                
                return response()->json(['error' => null, 'data' => $livePrice], 200);    
            }
            return response()->json(['error' => null, 'data' => ''], 500);
        }
        
         public function getNONBasmatiState()
        {
            $ricename = RiceName::select('id')->where('type', 'non-basmati')->pluck('id')->toArray();
            $lastRecord = LivePrice::where('name' ,'!=',0)->where('form' , '!=' , 0)->where('min_price', '!=', null)->where('max_price', '!=', null)->get()->last();
            if($lastRecord != null){
                $lastEnteredRecord = $lastRecord->created_at->format('Y-m-d');
                
                $livePrice = LivePrice::whereDate('created_at',$lastEnteredRecord)->where('min_price', '!=', null)->where('state_order', '!=', null)->where('max_price', '!=', null)->orderBy('state_order' , 'ASC')->whereIn('name', $ricename)->get()->map(function($query){
                    return $query->state;
                });

                if( count($livePrice) > 0 ){
                    $livePrice = array_unique($livePrice->toArray());
                    $livePrice = array_values($livePrice);
                }else{
                    $livePrice = [];
                }
                
                return response()->json(['error' => null, 'data' => $livePrice], 200);    
            }
            return response()->json(['error' => null, 'data' => ''], 500);
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
            User::where(['id' => $request->id])->update(['userToken' => $request->userToken]);
            return response()->json(['error' => null, 'message' => "Token updated successfully..."]);
        }
        
        // Update User Token

        public function saveOrder(Request $request)
        {
            $today = Carbon::now();
            $planModel = Plan::find($request->plan_id);
            $subPlanModel = SubPlan::find($request->sub_plan_id);
            $startDate = $today->format('Y-m-d');
            $subPlans = json_decode($planModel->sub_plan, true);
            $subPlanPrice = $subPlans[$request->sub_plan_id]['offerPrice'];
            
            if ($subPlanModel->name === "1 Year") {
                $endDate = $today->addYear(1)->format('Y-m-d');
            } else {
                if ($subPlanModel->name === "6 Month") {
                    
                    $endDate = $today->addMonth(6)->format('Y-m-d');
                } else {
                    $endDate = $today->addMonth(1)->format('Y-m-d');
                }
            }
            
            $orderModel = new Order;
            $orderModel->user_id = $request->user_id;
            $orderModel->transaction_id = $request->transaction_id;
            $orderModel->plan_id = $request->plan_id;
            $orderModel->sub_plan_id = $request->sub_plan_id;
            $orderModel->plan_name = $planModel->plan_name;
            $orderModel->start_date = $startDate;
            $orderModel->end_date = $endDate;
            $orderModel->sub_plan_name = $subPlanModel->name;
            $orderModel->sub_plan_price = $subPlanPrice;
            $orderModel->status = 1;

            if ($orderModel->save()) {
                User::where(['id' => $request->user_id])->update(['expired_on' => $endDate]);
                return response()->json(['status' => 'success', 'last_inserted_id' => $orderModel->id], 200);
            }
            return response()->json(['status' => 'error'], 500);
        }
        
        public function updateUserTokenById(Request $request)
        {
            if($request != null){
                if( $request->has('id') ){
                    if( $request->id != '' && $request->id != 'undefined' ){
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
        public function sendNotification( $message , $token , $from , $to ){
            $url = "https://fcm.googleapis.com/fcm/send";
			// $token = "cGzzg20-RwOJ-1HnD5sfaO:APA91bHASbUPacqon9gT3G93vqa10TPBeky599w8lSw5D5KYUT1SXmFq_2iEpArVaMm4eB4-PP-Fs-1hE82JEW3y1k53yhMRPkmZSLTGMG1B-XzFUtyvdiJwA8JDSZ1P2Y2JFRfwGXcd";
            $serverKey = 'AAAA10hB_8I:APA91bHVSnAJjacznL6i3p9dWnKvJeceYJlTbwt_rvyq6Nx8tOPsMlxtYPqHzAJRAazC5JJof9PZHaw_uo1qbNkKK4YgJLKN_39ozcIlbCpt3YQ36Y5rT6ftegC0nnEiOZ-dYsYqFWcV';
            $title = "Message";
            $body = $message;
            
            $notification = [
                'title' => $title ,
                'from' => $from ,
                'to' => $to,
                'data' => ['messageFrom' => $from],
                'body' => $body,
                'sound' => 'default',
                'badge' => '1'];
            
            $arrayToSend = [
                'to' => $token,
                'notification' => $notification,
                'data' => [ 'messageFrom' => $from],
                'priority'=>'high'];

            $json = json_encode($arrayToSend);
            
            $headers = [];
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
        
        // Save Message
        public function saveMessage(Request $request)
        {
            $userTo = User::where('id' , $request->to)->first();
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
                
                $result = self::sendNotification($request->message , $userTo->user_token, $request->from, $request->to);
                
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
        
        public function getUserMessageCount($userId){
            $message = Message::where(['to' => $userId])->where(['seen' => 0])->get()->count();
            return response()->json(['status' => 'success','data' => $message],200);
        }
        
        public function getMessagesByIds($from, $to)
        {
            Message::where(['from' => $from ,'to' => $to])->update(['seen' => 1]);
            Message::where(['from' => $to ,'to' => $from])->update(['seen' => 1]);

            $userMessageData = Message::where(['from' => $to ,'to' => $from] )->orWhere(function($query) use ($from , $to){
                return $query->where(['from' => $from ,'to' => $to]);
            } )->orderBy('created_at', 'ASC')->get()->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d');
            });
            
            $getId = ($from == 1) ? $to : $from;
            
            $userId = ($from == 1) ? $to : $from; 
            
            $user = User::select(['id','name','email','mobile','user_token'])->where('id',$getId)->first();
            if( $userMessageData->count() == 0 ){
                $messageModel = new Message;
                $messageModel->from = 1;
                $messageModel->to = $userId;
                $messageModel->seen = 1;
                $messageModel->message = 'Welcome to SNTC chat support. How may we help you today ?';
                $messageModel->status = 0;
                $messageModel->save();
                
                $userMessageData = Message::where(['from' => $to ,'to' => $from] )->orWhere(function($query) use ($from , $to){
                    return $query->where(['from' => $from ,'to' => $to]);
                } )->orderBy('created_at', 'ASC')->get()->groupBy(function($date) {
                    return Carbon::parse($date->created_at)->format('Y-m-d');
                });
            }
            
            return response()->json(['status' => 'success', 'from'=> $user,'data' => $userMessageData ],200);
            
        }
        
        public function getMessageContacts(){
            $data = [];
            $users = Message::orderBy('id','DESC')->get()->map(function($query){
                return $query->from;
            })->toArray();
            $arrayUniqueUsers = array_unique($users);
            // dd($arrayUniqueUsers);

            foreach($arrayUniqueUsers as $key => $user){
                if( $user!= 1 ){
                    // $data[][$user]['user'] = $user;
                    $userDetails = User::find($user);
                    if($userDetails){
                        $unseenMessage1 = Message::where('from','=',$user)->where('seen' ,0)->get()->count();
                        $unseenMessage2 = Message::where('to','=',$user)->where('seen' ,0)->get()->count();

                        $message = Message::where('from','=',$user)->orWhere('to','=',$user)->latest()->first(['message','created_at']);
                        $data[] = ['user' => $user,'name' => $userDetails->name,'email' => $userDetails->email,'companyname' => $userDetails->companyname,'last_message' => $message->message,'unseenMessage' => ($unseenMessage1+$unseenMessage2) ];
                    }
                }
            }
            return response()->json(['status' => 'success', 'data' => $data],200);
        }
        
        public function getMessageContactsRefator(){   // Created By Jaskaran To Refactor Code and tests 
            $messageWithUser = Message::where('from','!=',1)->with('user_rel')
            ->orderBy('id','DESC')->get()->unique('from');
            $newColl = $messageWithUser->mapToDictionary(function($query){
                $messageDetails = $this->getMessgaeDetails($query->user_rel->id);
                return [$query->user_rel->id => [
                            'user' =>   $query->user_rel->id,
                            'name' =>   $query->user_rel->name,
                            'email' =>  $query->user_rel->email,
                            'companyname' => $query->user_rel->companyname,
                            'last_message' => $messageDetails['latestMessage'],
                            'unseenMessage'=> $messageDetails['unseenMessage']
                        ]
                    ];
            });
            return response()->json(['status' => 'success', 'data' => $newColl],200);
        }
        
        public function getMessgaeDetails($userId){
            return ['unseenMessage' => Message::where('from','=',$userId)->where('seen' , 0)->get()->count(),
            'latestMessage' => Message::where('from','=',$userId)->orWhere('to' , '=' , $userId)->latest()->first()->message];
        }
        
        public function checkUserExpired($userId){
            $user = User::where('id' , $userId)->first();
            return response()->json(['status' => true , 'data' => $user->expired_on]);
        }
        
        public function getPriceStates(){
            
            $lastRecord = LivePrice::where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('id' , 'DESC')->first();

            if( $lastRecord != null ){
                $lastDate = Carbon::parse($lastRecord->created_at)->format('Y-m-d');
                
                $prices = LivePrice::whereDate('created_at' , $lastDate)->where('min_price', '!=', null)->where('max_price', '!=', null)->with(['name_rel' => function($query){
                    return $query->get();
                },'form_rel' => function ($query) {
                        return $query->orderBy('id', "ASC")->get();
                    }
                ])->get()->groupBy('state');    

                return response()->json(['status' => true , 'data' => $prices]);
            }
            return response()->json(['status' => true , 'data' => '']);
        }
        
        public function getTransportStates(){
            $port = Port::whereDate('created_at' , Carbon::today()->format('Y-m-d'))->where('route' , '!=' , '0')->where('state_order' ,'!=', null)->where('price' , '!=' , '0')->get()->sortBy('state_order');
            if( $port->count() == 0){
                $lastRecord = Port::orderBy('id', 'DESC')->where('route' , '!=' , '0')->where('price' , '!=' , '0')->orderBy('state_order')->first();
                $lastCreatedDate = $lastRecord->created_at;
                
                $port = Port::whereDate('created_at' , $lastCreatedDate)->where('route' , '!=' , '0')->where('state_order' ,'!=', null)->where('price' , '!=' , '0')->get()->sortBy('state_order');
            }
            
            $sortedArray = [];
            $port = $port->groupBy('state');
            foreach($port as $k => $v){
                $sortedArray[] = [$k => $v];        
            }
            return response()->json(['status' => true , 'data' => $sortedArray ]);
        }
        
        public function getPortDetails($state){
            $lastUpdatedDate = Port::orderBy('id' , 'DESC')->first();
            if( $lastUpdatedDate != null ){
                $lastDate = ($lastUpdatedDate->created_at)->format('Y-m-d');   
            }
            
            $port = Port::whereDate( 'created_at' , $lastDate )->where( 'state' , $state )->where( 'price' ,'!=' , '0' )->where( 'route' ,'!=' ,'0' )->get();
            
            return response()->json(['status' => true , 'data' => $port ]);
        }

        public function getUserPlan($userId){
            $order = Order::where('user_id' , $userId)->whereDate('end_date' ,'>=',Carbon::now()->format('Y-m-d'))->get();
            return response()->json( ['status' => true , 'data' => $order] );
        }   
     
        public function getAllBasmatiPrice($state)
        {
            $ricetype = 'basmati';
            $processedData = [];
            $lastRecord = LivePrice::where('name' ,'!=', '0')->where('form' , '!=' , '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('id' , 'DESC')->first();

            if ($lastRecord != null) {
                $prices = LivePrice::where('name' ,'!=', '0')->where('form' , '!=' , '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->with(['name_rel' => function($query) use($ricetype){
                    return $query->where('type' , $ricetype)->get();
                },'form_rel' => function ($query) use ($ricetype) {
                        return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
                    }
                ])->where('state' , $state)->whereDate('created_at',$lastRecord->created_at->format('Y-m-d'))->get();
                $lastToLastDate = LivePrice::where('name' ,'!=', '0')->where('form' , '!=' , '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('created_at', 'DESC')
                ->whereDate('created_at', '<',$lastRecord->created_at->format('Y-m-d'))->get();

                if (!$lastToLastDate->isEmpty()) {

                    $data = LivePrice::where('min_price', '!=', null)->where('max_price', '!=', null)->with([
                        'name_rel' => function($query){
                            // return $query->orderBy('order', 'asc')->get();
                            return $query->get();
                        },'form_rel' => function ($query) use ($ricetype) {
                            return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
                        }
                    ])->where(['state' => $state])->where(DB::raw('date(created_at)'),
                        $lastRecord->created_at->format('Y-m-d'))->get();

                    foreach ($data->sortBy('name_rel.order') as $k => $v) {
                        if ($v->name_rel != null && $v->state != null && $v->form_rel != null) {
                            if ($state == $v->state || strtoupper($state) == $v->state ) {
                                $replaceHignfn = explode('-', $v->name_rel->type);
                                $implodeUnderscore = implode('_', $replaceHignfn);
                                $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                            }
                        }
                    }

                    $fiilteredProcessedData = [];
                    foreach ($data->sortBy('form_rel.order') as $k => $v) {
                        if ($v->name_rel != null && $v->state != null && $v->form_rel != null) {
                            if ($state == $v->state || strtoupper($state) == $v->state ) {
                                $replaceHignfn = explode('-', $v->name_rel->type);
                                $implodeUnderscore = implode('_', $replaceHignfn);
                                $fiilteredProcessedData[$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                            }
                        }
                    }
                    $newProcessed = [];
                    foreach($processedData as $k => $v){
                        foreach($v as $kk => $vv){
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
                                }else{
                                    foreach($val as $kk => $vv ){
                                        // dd($processedData[$k][$key][$kk] );
                                        if( $kk != 0 ){
                                          $processedData[$k][$key][$kk]['isHide'] = 'true'; 
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $newProccessedData = [];
                    
                    $newData = collect($processedData)->map(function($item){
                        return collect($item)->map(function($innerItem) use ($item){
                            $onlyValues = array_values($innerItem);
                            $onlyKeys = array_keys($innerItem);
                            foreach($onlyValues as $k => $v){
                                if( $k == 0 ){
                                    $onlyValues[$k]['is_hide'] = 'false';        
                                }else{
                                    $onlyValues[$k]['is_hide'] = 'true';
                                }
                            }
                            
                            
                            $data = array_combine( $onlyKeys, $onlyValues);
                            return $data;
                        });
                    })->toArray();

                    $order = [];
                    foreach($newData as $k => $v){
                        foreach($v as $kk => $vv){
                            $order[$k][] = [ $kk => $vv] ;
                        }
                    }
                    $myNewData = [];
                    foreach($order as $k => $v){
                        foreach($v as $kk => $vv){
                            $newDataProcess = [];
                            foreach($vv as $key => $value){
                                foreach($value as $ke => $val){
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

        public function getAllNONBasmatiPrice($state){
            $ricetype = 'non-basmati';
            $processedData = [];
            $lastRecord = LivePrice::where('name' ,'!=', '0')->where('form' , '!=' , '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('id' , 'DESC')->first();

            if ($lastRecord != null) {
                $prices = LivePrice::where('name' ,'!=', '0')->where('form' , '!=' , '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->with(['name_rel' => function($query) use($ricetype){
                    return $query->where('type' , $ricetype)->get();
                },'form_rel' => function ($query) use ($ricetype) {
                        return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
                    }
                ])->where('state' , $state)->whereDate('created_at',$lastRecord->created_at->format('Y-m-d'))->get();
                $lastToLastDate = LivePrice::where('name' ,'!=', '0')->where('form' , '!=' , '0')->where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('created_at', 'DESC')
                ->whereDate('created_at', '<',$lastRecord->created_at->format('Y-m-d'))->get();

                if (!$lastToLastDate->isEmpty()) {

                    $data = LivePrice::where('min_price', '!=', null)->where('max_price', '!=', null)->with([
                        'name_rel' => function($query){
                            // return $query->orderBy('order', 'asc')->get();
                            return $query->get();
                        },'form_rel' => function ($query) use ($ricetype) {
                            return $query->orderBy('id', "ASC")->where('type', $ricetype)->get();
                        }
                    ])->where(['state' => $state])->where(DB::raw('date(created_at)'),
                        $lastRecord->created_at->format('Y-m-d'))->get();

                    foreach ($data->sortBy('name_rel.order') as $k => $v) {
                        if ($v->name_rel != null && $v->state != null && $v->form_rel != null) {
                            if ($state == $v->state || strtoupper($state) == $v->state ) {
                                $replaceHignfn = explode('-', $v->name_rel->type);
                                $implodeUnderscore = implode('_', $replaceHignfn);
                                $processedData[$implodeUnderscore][$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                            }
                        }
                    }

                    $fiilteredProcessedData = [];
                    foreach ($data->sortBy('form_rel.order') as $k => $v) {
                        if ($v->name_rel != null && $v->state != null && $v->form_rel != null) {
                            if ($state == $v->state || strtoupper($state) == $v->state ) {
                                $replaceHignfn = explode('-', $v->name_rel->type);
                                $implodeUnderscore = implode('_', $replaceHignfn);
                                $fiilteredProcessedData[$v->name_rel->name][$v->form_rel->form_name][$v->created_at->format('Y-m-d')] = $v;
                            }
                        }
                    }
                    $newProcessed = [];
                    foreach($processedData as $k => $v){
                        foreach($v as $kk => $vv){
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
                                }else{
                                    foreach($val as $kk => $vv ){
                                        // dd($processedData[$k][$key][$kk] );
                                        if( $kk != 0 ){
                                          $processedData[$k][$key][$kk]['isHide'] = 'true'; 
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $newProccessedData = [];
                    
                    $newData = collect($processedData)->map(function($item){
                        return collect($item)->map(function($innerItem) use ($item){
                            $onlyValues = array_values($innerItem);
                            $onlyKeys = array_keys($innerItem);
                            foreach($onlyValues as $k => $v){
                                if( $k == 0 ){
                                    $onlyValues[$k]['is_hide'] = 'false';        
                                }else{
                                    $onlyValues[$k]['is_hide'] = 'true';
                                }
                            }
                            
                            
                            $data = array_combine( $onlyKeys, $onlyValues);
                            return $data;
                        });
                    })->toArray();

                    $order = [];
                    foreach($newData as $k => $v){
                        foreach($v as $kk => $vv){
                            $order[$k][] = [ $kk => $vv] ;
                        }
                    }
                    $myNewData = [];
                    foreach($order as $k => $v){
                        foreach($v as $kk => $vv){
                            $newDataProcess = [];
                            foreach($vv as $key => $value){
                                foreach($value as $ke => $val){
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
        	$lastRecord = LivePrice::where('min_price', '!=', null)->where('max_price', '!=', null)->orderBy('id' , 'DESC')->first();

            if( $lastRecord != null ){
                $lastDate = Carbon::parse($lastRecord->created_at)->format('Y-m-d');
                
                $prices = LivePrice::whereDate('created_at' , $lastDate)->where('min_price', '!=', null)->where('max_price', '!=', null)->with(['name_rel' => function($query){
                    return $query->get();
                },'form_rel' => function ($query) {
                        return $query->orderBy('id', "ASC")->get();
                    }
                ])->get()->groupBy('state');    
                $array_keys = array_keys($prices->toArray());
	

	        	return response()->json([ 'status' => 'success' , 'data' => $array_keys ]);
            }
            return response()->json(['status' => true , 'data' => '']);



        	$livePrice = LivePrice::orderBy('state_order')->where('min_price', '!=', null)->where('max_price', '!=', null)->select('state')->get()->groupBy('state');
        	dd($livePrice);
        	$array_keys = array_keys($livePrice->toArray());
        	return response()->json(['status' => 'success' , 'data' => $array_keys ]);
        }
        public function getPricesByState($state = 'PUNJAB-HARYANA')
        {
        	$lastPrice  = LivePrice::last();
        	dd($lastPrice);
        }

        public function getPortsInOrder(){
            try {
        
                $lastDate = Port::where('price', '!=', 0)->orderByDesc('id')->first('created_at');
                $ports    = Port::where('price','!=',0)->where('created_at','LIKE','%'.$lastDate->created_at.'%')
                            ->orderBy('state_order')->get()->groupBy(['state','route']);
                
                return response()->json(['status' => true, 'data' => $ports]);
            
            }catch (Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }