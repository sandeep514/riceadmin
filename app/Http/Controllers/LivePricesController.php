<?php

namespace App\Http\Controllers;

use App\LivePrice;
use App\RiceForm;
use App\RiceName;
use App\LivePriceStatusMessage;
use App\LivePricesOpeningClosing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class LivePricesController extends Controller
{
    // public function index(Request $request, $riceName = null){
    //     $RiceForm= RiceForm::where('status' , 1)->get();
    //     $RiceName= RiceName::get();

    //     $livePrice = LivePrice::get()->groupBy('state');

    //     $riceModel = null;
    //     $riceForms = null;
    //     $todaysPrices = null;
    //     $lastPrices = null;
    //     $todayYear = Carbon::now()->format('Y');
    //     $lastFiveYear = Carbon::now()->subYear(5)->format('Y');

    //     $lastYears = [];

    //     for($i = $todayYear; $i >= $lastFiveYear; $i--){
    //         $lastYears[] = (int)$i;
    //     }

    //     if($riceName != null){
    //         $riceModel = RiceName::find($riceName);
    //         $riceForms = RiceForm::where('status' , 1)->where(['type'=>$riceModel->type])->get();
    //         if( LivePrice::get()->count() == 0 ){
    //             return view('live_prices.create',['prices'=>[],'riceModel'=>$riceModel,'riceForm'=>$riceForms,'today_price'=>$todaysPrices,'lastPrices' => $lastPrices]);
    //         }
    //         $todaysPrices = LivePrice::where(['name'=>$riceName])->where(DB::raw('date(created_at)'),Carbon::now()->format('Y-m-d'))->get();
    //         $lastAvaibleRecord = LivePrice::orderBy('created_at' , "DESC")->first();
            
    //         $lastAvailableDate = date_format(date_create($lastAvaibleRecord->created_at) , 'Y-m-d');
    //         $lastPrices = LivePrice::where(['name'=>$riceName])->with(['form_rel','name_rel'])->whereDate('created_at' , $lastAvailableDate)->get();
    //     }

    //     if($request->has('from')){
    //         $prices = LivePrice::with(['name_rel','form_rel'])->where('min_price' ,'!=', 0)->where('max_price' ,'!=', 0)->whereBetween(DB::raw('date(created_at)'),[Carbon::parse($request->from)->format('Y-m-d'), Carbon::parse($request->to)->format('Y-m-d')])->get();
    //     }else{
    //         $prices = LivePrice::with(['name_rel','form_rel'])->where('min_price' ,'!=', 0)->where('max_price' ,'!=', 0)->where(DB::raw('date(created_at)'),Carbon::now()->format('Y-m-d'))->get();
    //     }
    //     return view('live_prices.create',['lastYears' => $lastYears,'livePrice'=>$livePrice,'prices'=>$prices,'riceModel'=>$riceModel,'riceForm'=>$riceForms,'today_price'=>$todaysPrices,'lastPrices' => $lastPrices]);
    // }

    // 11 nov 2025

    public function index(Request $request, $riceName = null)
    {
        $RiceForm = RiceForm::where('status', 1)->get();       // all active forms
        $RiceName = RiceName::orderedForSelect(true)->get();

        // lightweight grouped live prices (select only necessary columns)
        // $livePrice = LivePrice::select('id','state','min_price','max_price')->get()->groupBy('state');
        $livePrice = LivePrice::selectRaw('state_order,state, MIN(min_price) as min_price, MAX(max_price) as max_price')
            ->where('min_price', '>', 0)
            ->where('max_price', '>', 0)
            ->orderBy('state_order')
            ->groupBy('state')
            ->get()
            ->keyBy('state');

            // init variables
        $riceModel = null;
        $riceForm  = null;        // note: singular key expected by your view
        $today_price = null;
        $lastPrices  = null;


         // today's prices for this rice
        $today_price = LivePrice::whereDate('created_at', Carbon::now()->format('Y-m-d'))
            ->first();


        // years list (current -> current-5)
        $lastYears = range(Carbon::now()->year, Carbon::now()->subYears(5)->year);

        // If riceName provided
        if ($riceName !== null) {
            $riceModel = RiceName::find($riceName);
            if (!$riceModel) {
                abort(404, 'Rice not found');
            }

            // get forms for this rice type
            $riceForm = RiceForm::where('status', 1)->where('type', $riceModel->type)->get();

            // if no live prices at all, show empty create view
            if (!LivePrice::exists()) {
                return view('live_prices.create', [
                    'prices'     => [],
                    'riceModel'  => $riceModel,
                    'riceForm'   => $riceForm,
                    'today_price'=> $today_price,
                    'lastPrices' => $lastPrices,
                    'livePrice'  => $livePrice,
                    'lastYears'  => $lastYears
                ]);
            }


            // last available record's date (safe)
            $lastAvaibleRecord = LivePrice::latest('created_at')->first();

            if ($lastAvaibleRecord) {
                $lastAvailableDate = Carbon::parse($lastAvaibleRecord->created_at)->format('Y-m-d');

                $lastPrices = LivePrice::where('name', $riceName)
                    ->with(['form_rel','name_rel'])
                    ->whereDate('created_at', $lastAvailableDate)
                    ->orderBy('updated_at', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->get();
            }
        }

        // Build base query for price list
        // $query = LivePrice::with(['name_rel','form_rel'])
        //     ->where('min_price', '!=', 0)
        //     ->where('max_price', '!=', 0);

        // if ($request->has('from') && $request->has('to')) {
        //     $from = Carbon::parse($request->from)->startOfDay();
        //     $to   = Carbon::parse($request->to)->endOfDay();
        //     $query->whereBetween('created_at', [$from, $to]);
        // } else {
        //     $query->whereDate('created_at', Carbon::today());
        // }

        // $prices = $query->get();
        $LivePriceStatusMessage = LivePriceStatusMessage::orderBy('id' , 'desc')->first();
        
        $prices = collect();

        return view('live_prices.create', [
            'lastYears'  => $lastYears,
            'livePrice'  => $livePrice,
            'prices'     => $prices,
            'riceModel'  => $riceModel,
            'riceForm'   => $riceForm,   
            'today_price'=> $today_price,
            'lastPrices' => $lastPrices,
            'RiceForm'   => $RiceForm,
            'RiceName'   => $RiceName,
            'LivePriceStatusMessage' => $LivePriceStatusMessage
        ]);
    }



    // 11 nov 2025
    public function savePrice(Request $request){
        $now = Carbon::now(config('app.timezone', 'Asia/Kolkata'));
        $todayDate = $now->format('Y-m-d');
        $currentTimestamp = $now->format('Y-m-d H:i:s');
        $lastAvailableDate ='';
        $lastAvaibleRecord = LivePrice::where('min_price' ,'!=' ,0  )->orderBy('created_at' , "DESC")->first();
        $sortedStateData = [];
        $sortedNameData = [];
        $data_state_order = LivePrice::get()->sortBy('state_order');
        $data_name_order = LivePrice::with(['name_rel' , 'form_rel'])->get()->sortBy('name_order');
        
        $cropYear  = (int)$request->cropYear;
        $cropGrade = (int)$request->cropGrade;


        foreach($data_state_order as $k => $v){
            if( $v->state_order != null ){
                $sortedStateData[$v->state_order] = $v->state; 
            }
        }
        
        foreach($data_name_order as $k => $v){
            if( $v->name_order != null  ){
                $sortedNameData[$v->name_order] = $v->name; 
            }
        }

        
        if( $lastAvaibleRecord != null ){
            $lastAvailableDate = date_format(date_create($lastAvaibleRecord->created_at) , 'Y-m-d');    
        }
        $openingOrClosing = [];
        if( $todayDate == $lastAvailableDate ){
            foreach($request->min as $state => $values){
                foreach($values as $form => $price){
                    $userDetails = LivePrice::where(['state' => $state , 'form' => $form , 'name' => $request->name])->whereDate( 'created_at' , $todayDate )->first();

                    $this->createAdminLivePriceEntry(
                        $request,
                        $state,
                        $form,
                        $price,
                        $currentTimestamp,
                        $userDetails
                    );

                    if (isset($request->opening[$state][$form]) || isset($request->closing[$state][$form])) {
                        $openingOrClosing[] = [
                            'name' => $request->name,
                            'state' => $state,
                            'form' => $form,
                            'cropYear' => $request->cropYear[$state][$form],
                            'opening' => $request->opening[$state][$form] ?? '',
                            'closing' => $request->closing[$state][$form] ?? '',
                        ];
                    }
                    
                }
            }
        }else{
            $lastUpdatedPrice = LivePrice::whereDate( 'created_at' , $lastAvailableDate )->get();

            if( $lastUpdatedPrice->count() > 0 ){
                foreach( $lastUpdatedPrice as $k => $v ){

                    LivePrice::create([
                        'name'      => $v->name, 
                        'form'      => $v->form,
                        'is_updated_by_admin' => 1,
                        'min_price' => $v->min_price,
                        'max_price' => $v->max_price,
                        'cropYear'  => $v->cropYear,
                        'cropGrade' => $v->cropGrade,
                        'state'     => $v->state,
                        'opening'   => $v->opening??'',
                        'closing'   => $v->closing??'',
                        'monthStart'   => $v->monthStart??'',
                        'monthEnd'   => $v->monthEnd??'',
                        'up_down'   => $v->up_down,
                        'created_at' => $currentTimestamp,
                        'updated_at' => $currentTimestamp
                    ]);

                    if( isset($v->opening) || isset($v->closing) ){
                        $openingOrClosing[] = [
                            'name'      => $v->name,
                            'form'      => $v->form ,
                            'state'     => $v->state ,
                            'cropYear'  => $v->cropYear,
                            'opening'   => $v->opening??'',
                            'closing'   => $v->closing??'' 
                        ];
                    }
                }     
                foreach($request->min as $state => $values){
                    foreach($values as $form => $price){
                        // $priceModel = LivePrice::where(DB::raw('date(+)'),Carbon::now()->format('Y-m-d'))->firstOrNew(['state'=>$state,'name'=>$request->name,'form'=>$form]);
                        // $priceModel->name = $request->name;
                        // $priceModel->form = $form;
                        // $priceModel->min_price = $price;
                        // $priceModel->max_price = $request->max[$state][$form];
                        // $priceModel->state = $state;
                        // $priceModel->up_down = $request->up_down[$state][$form];
                        // $priceModel->save();
                        
                        $previousRow = LivePrice::where([
                            'state' => $state,
                            'form' => $form,
                            'name' => $request->name,
                        ])->whereDate('created_at', $todayDate)
                            ->orderBy('updated_at', 'desc')
                            ->orderBy('id', 'desc')
                            ->first();

                        $this->createAdminLivePriceEntry(
                            $request,
                            $state,
                            $form,
                            $price,
                            $currentTimestamp,
                            $previousRow
                        );

                        if( isset($request->opening[$state][$form]) || isset($request->closing[$state][$form]) ){
                            $openingOrClosing[] = [
                                'name' => $request->name,
                                'state' => $state ,
                                'form' => $form ,
                                'cropYear'  => $request->cropYear[$state][$form],
                                'opening' => $request->opening[$state][$form]??'',
                                'closing' => $request->closing[$state][$form]??'' 
                            ];
                        }
                    }
                }
                
            }else{
                foreach($request->min as $state => $values){
                    foreach($values as $form => $price){
                        $priceModel = new LivePrice();
                        $priceModel->name = $request->name;
                        $priceModel->form = $form;
                        $priceModel->min_price  = $price;
                        $priceModel->cropYear   = $cropYear;
                        $priceModel->cropGrade  = $cropGrade;
                        $priceModel->max_price  = $request->max[$state][$form];
                        $priceModel->state      = $state;
                        $priceModel->up_down    = $request->up_down[$state][$form];
                        $priceModel->opening    = $request->opening[$state][$form]??'';
                        $priceModel->closing    = $request->closing[$state][$form]??'';
                        $priceModel->monthStart    = $request->monthStart[$state][$form]??'';
                        $priceModel->monthEnd    = $request->monthEnd[$state][$form]??'';
                        $priceModel->created_at = $currentTimestamp;
                        $priceModel->updated_at = $currentTimestamp;
                        $priceModel->save();

                        if (!empty($request->opening[$state][$form]) && !empty($request->closing[$state][$form])) {
                            $openingOrClosing[] = [
                                'name' => $request->name,
                                'state' => $state ,
                                'form' => $form ,
                                'cropYear'  => $cropYear,
                                'opening' => $request->opening[$state][$form]??'',
                                'closing' => $request->closing[$state][$form]??'' 
                            ];
                        }
                    }
                }   
            }  
        }
        LivePrice::where('min_price' , null)->where('max_price' , null)->delete();
        LivePrice::where('name' , 0)->where('form' , 0)->where('min_price' , 0)->where('max_price' , 0)->delete();
        
        // $lastAvaibleRecord = LivePrice::orderBy('created_at' , "DESC")->first();

        
        // $lastPrices = LivePrice::where(['name'=>$request->name])->with(['form_rel','name_rel'])->whereDate('created_at' , $lastAvailableDate)->get();
        
        // dd($lastAvailableDate);
        // foreach($request->min as $state => $values){
        //     foreach($values as $form => $price){
        //         $priceModel = LivePrice::where(DB::raw('date(+)'),Carbon::now()->format('Y-m-d'))->firstOrNew(['state'=>$state,'name'=>$request->name,'form'=>$form]);
        //         $priceModel->name = $request->name;
        //         $priceModel->form = $form;
        //         $priceModel->min_price = $price;
        //         $priceModel->max_price = $request->max[$state][$form];
        //         $priceModel->state = $state;
        //         $priceModel->up_down = $request->up_down[$state][$form];
        //         $priceModel->save();
        //     }
        // }
        
        foreach($sortedStateData as $k => $v){
            LivePrice::where('state' , $v)->whereDate('created_at' , $todayDate)->update(['state_order' => $k]);
        }
        foreach($sortedNameData as $k => $v){
            LivePrice::where('name' , $v)->whereDate('created_at' , $todayDate)->update(['name_order' => $k]);    
        }

        
        // openingOrClosing
        // dd($openingOrClosing);

        // foreach($openingOrClosing as $k => $v){
            LivePricesOpeningClosing::upsert(
                $openingOrClosing,
                ['name', 'state', 'form', 'cropYear'],
                [
                    'opening',
                    'closing' => DB::raw("
                        COALESCE(
                            NULLIF(VALUES(closing), ''),
                            closing
                        )
                    ")
                ]
            );
        // }/


        Session::flash('success','Success|Price saved successfully!');
        return back();
    }


    public function savePriceSingle(Request $request)
    {
        $state          =   $request->state;
        $name           =   $request->name;
        $form           =   $request->form;
        $min_price      =   $request->min[$state][$form]; 
        $max_price      =   $request->max[$state][$form];

        $cropYear       =   $request->cropYear[$state][$form];
        $cropGrade      =   $request->cropGrade[$state][$form];
        $opening        =   $request->opening[$state][$form]??'';
        $closing        =   $request->closing[$state][$form]??'';
        $monthStart     =   $request->monthStart[$state][$form]??'';
        $monthEnd       =   $request->monthEnd[$state][$form]??'';
        $up_down        =   (array_key_exists($form, $request->up_down[$state])? $request->up_down[$state][$form] : 'up' );


        $now = Carbon::now(config('app.timezone', 'Asia/Kolkata'));
        $todayDate = $now->format('Y-m-d');
        $updatedTime = $now->format('Y-m-d H:i:s');

        $livePrices = LivePrice::where([
                'name'      => $name,
                'form'      => $form,
                'state'     => $state
            ])->whereDate('created_at' , $todayDate);

        $previousRow = $livePrices
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        LivePrice::create([
            'min_price' => $min_price,
            'max_price' => $max_price,
            'is_updated_by_admin' => 1,
            'cropGrade' => $cropGrade,
            'opening' => $opening,
            'closing' => $closing,
            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd,
            'up_down' => $up_down,
            'name' => $name,
            'form' => $form,
            'cropYear' => $cropYear,
            'state' => $state,
            'tradeFor' => $previousRow?->tradeFor ?? 1,
            'farmingType' => $previousRow?->farmingType ?? 1,
            'state_order' => $previousRow?->state_order,
            'name_order' => $previousRow?->name_order,
            'form_order' => $previousRow?->form_order,
            'status' => $previousRow?->status ?? 1,
            'created_at' => $updatedTime,
            'updated_at' => $updatedTime,
        ]);

        

        // Keep updated_at tied to the saved row only; touching all today's rows
        // can cause stale duplicates to be preferred on reload.

        // Insert or update live_price_closing based on name, form, cropYear, state
        if( $opening !== '' || $closing !== '' ){
            LivePricesOpeningClosing::upsert(
                [
                    'name'     => $name,
                    'form'     => $form,
                    'cropYear' => $cropYear,
                    'state'    => $state,
                    'opening'  => $opening,
                    'closing'  => $closing,
                ],
                ['name', 'form', 'cropYear', 'state'],
                [
                    'opening',
                    'closing' => DB::raw("
                        COALESCE(
                            NULLIF(VALUES(closing), ''),
                            closing
                        )
                    ")
                ]
            );
        }

        return response()->json(['status' => true , 'message' => 'data uploaded successfully']);
    }




    // public function savePrice(Request $request){
    //     $todayDate = Carbon::now()->format('Y-m-d');   
    //     $lastAvailableDate ='';
    //     $lastAvaibleRecord = LivePrice::where('min_price' ,'!=' ,0  )->orderBy('created_at' , "DESC")->first();
    //     $sortedStateData = [];
    //     $sortedNameData = [];
    //     $data_state_order = LivePrice::get()->sortBy('state_order');
    //     $data_name_order = LivePrice::with(['name_rel' , 'form_rel'])->get()->sortBy('name_order');
        
    //     $cropYear  = (int)$request->cropYear;
    //     $cropGrade = (int)$request->cropGrade;


    //     foreach($data_state_order as $k => $v){
    //         if( $v->state_order != null ){
    //             $sortedStateData[$v->state_order] = $v->state; 
    //         }
    //     }
        
    //     foreach($data_name_order as $k => $v){
    //         if( $v->name_order != null  ){
    //             $sortedNameData[$v->name_order] = $v->name; 
    //         }
    //     }

        
    //     if( $lastAvaibleRecord != null ){
    //         $lastAvailableDate = date_format(date_create($lastAvaibleRecord->created_at) , 'Y-m-d');    
    //     }
    //     $openingOrClosing = [];

    //     if( $todayDate == $lastAvailableDate ){
    //         foreach($request->min as $state => $values){
    //             foreach($values as $form => $price){
    //                 $userDetails = LivePrice::where(['state' => $state , 'form' => $form , 'name' => $request->name])->whereDate( 'created_at' , $todayDate )->first();

    //                 if( $userDetails ){
    //                     LivePrice::where(['state' => $state , 'form' => $form , 'name' => $request->name])->whereDate( 'created_at' , $todayDate )->update(['cropYear'  => $request->cropYear[$state][$form], 'cropGrade' => $request->cropGrade[$state][$form], 'min_price' => $price , 'max_price' => $request->max[$state][$form] , 'up_down' => $request->up_down[$state][$form],'monthStart' => $request->monthStart[$state][$form]??'','monthEnd' => $request->monthEnd[$state][$form]??'','opening' => $request->opening[$state][$form]??'','closing' => $request->closing[$state][$form]??'' ]);    
    //                 }else{
    //                     LivePrice::create([
    //                         'name'      => $request->name,
    //                         'form'      => $form,
    //                         'min_price' => $price, 
    //                         'cropYear'  => $request->cropYear[$state][$form],
    //                         'cropGrade' => $request->cropGrade[$state][$form],
    //                         'max_price' => $request->max[$state][$form],
    //                         'state'     => $state,
    //                         'opening'   => $request->opening[$state][$form]??'',
    //                         'closing'   => $request->closing[$state][$form]??'',
    //                         'monthStart'   => $request->monthStart[$state][$form]??'',
    //                         'monthEnd'   => $request->monthEnd[$state][$form]??'',
    //                         'up_down'   => (array_key_exists($form, $request->up_down[$state])? $request->up_down[$state][$form] : 'up' ),
    //                     ]);
    //                 }
                    
    //             }
    //         }
    //     }else{
    //         $lastUpdatedPrice = LivePrice::whereDate( 'created_at' , $lastAvailableDate )->get();

    //         if( $lastUpdatedPrice->count() > 0 ){
    //             foreach( $lastUpdatedPrice as $k => $v ){

    //                 LivePrice::create([
    //                     'name'      => $v->name, 
    //                     'form'      => $v->form,
    //                     'min_price' => $v->min_price,
    //                     'max_price' => $v->max_price,
    //                     'cropYear'  => $v->cropYear,
    //                     'cropGrade' => $v->cropGrade,
    //                     'state'     => $v->state,
    //                     'opening'   => $v->opening??'',
    //                     'closing'   => $v->closing??'',
    //                     'monthStart'   => $v->monthStart??'',
    //                     'monthEnd'   => $v->monthEnd??'',
    //                     'up_down'   => $v->up_down
    //                 ]);
    //             }     
    //             foreach($request->min as $state => $values){
    //                 foreach($values as $form => $price){
    //                     // $priceModel = LivePrice::where(DB::raw('date(+)'),Carbon::now()->format('Y-m-d'))->firstOrNew(['state'=>$state,'name'=>$request->name,'form'=>$form]);
    //                     // $priceModel->name = $request->name;
    //                     // $priceModel->form = $form;
    //                     // $priceModel->min_price = $price;
    //                     // $priceModel->max_price = $request->max[$state][$form];
    //                     // $priceModel->state = $state;
    //                     // $priceModel->up_down = $request->up_down[$state][$form];
    //                     // $priceModel->save();
                        
    //                     LivePrice::where(['state' => $state , 'form' => $form , 'name' => $request->name])->whereDate( 'created_at' , $todayDate )->update([
    //                         'cropYear'  => $request->cropYear[$state][$form], 
    //                         'cropGrade' => $request->cropGrade[$state][$form],
    //                         'min_price' => $price , 
    //                         'max_price' => $request->max[$state][$form] , 
    //                         'up_down'   => $request->up_down[$state][$form],
    //                         'opening'   => $request->opening[$state][$form]??'',
    //                         'closing'   => $request->closing[$state][$form]??'',
    //                         'monthStart'   => $request->monthStart[$state][$form]??'',
    //                         'monthEnd'   => $request->monthEnd[$state][$form]??'',
    //                     ]);

    //                     // LivePrice::where(['state' => $state , 'form' => $form , 'name' => $request->name])->create([
                        
    //                     // LivePrice::create([
    //                     //     'state' => $state , 
    //                     //     'form' => $form , 
    //                     //     'name' => $request->name,
    //                     //     'cropYear'  => $request->cropYear[$state][$form], 
    //                     //     'cropGrade' => $request->cropGrade[$state][$form],
    //                     //     'min_price' => $price , 
    //                     //     'max_price' => $request->max[$state][$form] , 
    //                     //     'up_down'   => $request->up_down[$state][$form],
    //                     //     'opening'   => $request->opening[$state][$form]??'',
    //                     //     'closing'   => $request->closing[$state][$form]??'',
    //                     //     'monthStart'   => $request->monthStart[$state][$form]??'',
    //                     //     'monthEnd'   => $request->monthEnd[$state][$form]??'',
    //                     // ]);
    //                 }
    //             }
                
    //         }else{
    //             foreach($request->min as $state => $values){
    //                 foreach($values as $form => $price){
    //                     $priceModel = new LivePrice();
    //                     $priceModel->name = $request->name;
    //                     $priceModel->form = $form;
    //                     $priceModel->min_price  = $price;
    //                     $priceModel->cropYear   = $cropYear;
    //                     $priceModel->cropGrade  = $cropGrade;
    //                     $priceModel->max_price  = $request->max[$state][$form];
    //                     $priceModel->state      = $state;
    //                     $priceModel->up_down    = $request->up_down[$state][$form];
    //                     $priceModel->opening    = $request->opening[$state][$form]??'';
    //                     $priceModel->closing    = $request->closing[$state][$form]??'';
    //                     $priceModel->monthStart    = $request->monthStart[$state][$form]??'';
    //                     $priceModel->monthEnd    = $request->monthEnd[$state][$form]??'';
    //                     $priceModel->save();
    //                 }
    //             }   
    //         }  
    //     }
    //     LivePrice::where('min_price' , null)->where('max_price' , null)->delete();
    //     LivePrice::where('name' , 0)->where('form' , 0)->where('min_price' , 0)->where('max_price' , 0)->delete();
        
    //     // $lastAvaibleRecord = LivePrice::orderBy('created_at' , "DESC")->first();

        
    //     // $lastPrices = LivePrice::where(['name'=>$request->name])->with(['form_rel','name_rel'])->whereDate('created_at' , $lastAvailableDate)->get();
        
    //     // dd($lastAvailableDate);
    //     // foreach($request->min as $state => $values){
    //     //     foreach($values as $form => $price){
    //     //         $priceModel = LivePrice::where(DB::raw('date(+)'),Carbon::now()->format('Y-m-d'))->firstOrNew(['state'=>$state,'name'=>$request->name,'form'=>$form]);
    //     //         $priceModel->name = $request->name;
    //     //         $priceModel->form = $form;
    //     //         $priceModel->min_price = $price;
    //     //         $priceModel->max_price = $request->max[$state][$form];
    //     //         $priceModel->state = $state;
    //     //         $priceModel->up_down = $request->up_down[$state][$form];
    //     //         $priceModel->save();
    //     //     }
    //     // }
        
    //     foreach($sortedStateData as $k => $v){
    //         LivePrice::where('state' , $v)->update(['state_order' => $k]);
    //     }
    //     foreach($sortedNameData as $k => $v){
    //         LivePrice::where('name' , $v)->update(['name_order' => $k]);    
    //     }
    //     Session::flash('success','Success|Price saved successfully!');
    //     return back();
    // }

    /**
     * Always insert a new live_prices row on admin save (preserve history).
     */
    private function createAdminLivePriceEntry(
        Request $request,
        string $state,
        $form,
        $price,
        string $currentTimestamp,
        ?LivePrice $previousRow = null
    ): LivePrice {
        return LivePrice::create([
            'name' => $request->name,
            'form' => $form,
            'min_price' => $price,
            'is_updated_by_admin' => 1,
            'cropYear' => $request->cropYear[$state][$form],
            'cropGrade' => $request->cropGrade[$state][$form],
            'max_price' => $request->max[$state][$form],
            'state' => $state,
            'opening' => $request->opening[$state][$form] ?? '',
            'closing' => $request->closing[$state][$form] ?? '',
            'monthStart' => $request->monthStart[$state][$form] ?? '',
            'monthEnd' => $request->monthEnd[$state][$form] ?? '',
            'up_down' => array_key_exists($form, $request->up_down[$state] ?? [])
                ? $request->up_down[$state][$form]
                : ($previousRow?->up_down ?? 'up'),
            'tradeFor' => $previousRow?->tradeFor ?? 1,
            'farmingType' => $previousRow?->farmingType ?? 1,
            'state_order' => $previousRow?->state_order,
            'name_order' => $previousRow?->name_order,
            'form_order' => $previousRow?->form_order,
            'status' => $previousRow?->status ?? 1,
            'created_at' => $currentTimestamp,
            'updated_at' => $currentTimestamp,
        ]);
    }

    public function delete($id){
        LivePrice::find($id)->delete();
        Session::flash('success','Success|Record deleted successfully!');
        return back();
    }

    public function updateMarketStatus($status)
    {
        $stausArray = [
            'open' => 1,
            'closed' => 2,
            'hold' => 3
        ]; 

        LivePriceStatusMessage::where('id' , 1)->update(['currentStatus' => $stausArray[$status], 'message' => $status]);
        Session::flash('success','Success|Record deleted successfully!');
        return back();
    }
}
