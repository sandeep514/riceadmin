<?php

namespace App\Http\Controllers;

use App\LivePrice;
use App\RiceForm;
use App\RiceName;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class LivePricesController extends Controller
{
    public function index(Request $request, $riceName = null){
        $RiceForm= RiceForm::where('status' , 1)->get();
        $RiceName= RiceName::get();
        $livePrice = LivePrice::get()->groupBy('state');

        $riceModel = null;
        $riceForms = null;
        $todaysPrices = null;
        $lastPrices = null;
        $todayYear = Carbon::now()->format('Y');
        $lastFiveYear = Carbon::now()->subYear(5)->format('Y');

        $lastYears = [];

        for($i = $todayYear; $i >= $lastFiveYear; $i--){
            $lastYears[] = (int)$i;
        }

        if($riceName != null){
            $riceModel = RiceName::find($riceName);
            $riceForms = RiceForm::where('status' , 1)->where(['type'=>$riceModel->type])->get();
            if( LivePrice::get()->count() == 0 ){
                return view('live_prices.create',['prices'=>[],'riceModel'=>$riceModel,'riceForm'=>$riceForms,'today_price'=>$todaysPrices,'lastPrices' => $lastPrices]);
            }
            $todaysPrices = LivePrice::where(['name'=>$riceName])->where(DB::raw('date(created_at)'),Carbon::now()->format('Y-m-d'))->get();
            $lastAvaibleRecord = LivePrice::orderBy('created_at' , "DESC")->first();
            
            $lastAvailableDate = date_format(date_create($lastAvaibleRecord->created_at) , 'Y-m-d');
            $lastPrices = LivePrice::where(['name'=>$riceName])->with(['form_rel','name_rel'])->whereDate('created_at' , $lastAvailableDate)->get();
        }

        if($request->has('from')){
            $prices = LivePrice::with(['name_rel','form_rel'])->where('min_price' ,'!=', 0)->where('max_price' ,'!=', 0)->whereBetween(DB::raw('date(created_at)'),[Carbon::parse($request->from)->format('Y-m-d'), Carbon::parse($request->to)->format('Y-m-d')])->get();
        }else{
            $prices = LivePrice::with(['name_rel','form_rel'])->where('min_price' ,'!=', 0)->where('max_price' ,'!=', 0)->where(DB::raw('date(created_at)'),Carbon::now()->format('Y-m-d'))->get();
        }
        return view('live_prices.create',['lastYears' => $lastYears,'livePrice'=>$livePrice,'prices'=>$prices,'riceModel'=>$riceModel,'riceForm'=>$riceForms,'today_price'=>$todaysPrices,'lastPrices' => $lastPrices]);
    }

    public function savePrice(Request $request){

        // dd("kjhkn"/);
        $todayDate = Carbon::now()->format('Y-m-d');   
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
        // dd($request->all());
        if( $todayDate == $lastAvailableDate ){
            foreach($request->min as $state => $values){
                foreach($values as $form => $price){
                    
                    $userDetails = LivePrice::where(['state' => $state , 'form' => $form , 'name' => $request->name])->whereDate( 'created_at' , $todayDate )->first();

                    if( $userDetails ){
                        LivePrice::where(['state' => $state , 'form' => $form , 'name' => $request->name])->whereDate( 'created_at' , $todayDate )->update(['cropYear'  => $request->cropYear[$state][$form], 'cropGrade' => $request->cropGrade[$state][$form], 'min_price' => $price , 'max_price' => $request->max[$state][$form] , 'up_down' => $request->up_down[$state][$form] ]);    
                    }else{
                        LivePrice::create([
                            'name'      => $request->name,
                            'form'      => $form,
                            'min_price' => $price, 
                            'cropYear'  => $request->cropYear[$state][$form],
                            'cropGrade' => $request->cropGrade[$state][$form],
                            'max_price' => $request->max[$state][$form],
                            'state'     => $state,
                            'up_down'   => (array_key_exists($form, $request->up_down[$state])? $request->up_down[$state][$form] : 'up' )
                        ]);
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
                        'min_price' => $v->min_price,
                        'max_price' => $v->max_price,
                        'cropYear'  => $v->cropYear,
                        'cropGrade' => $v->cropGrade,
                        'state'     => $v->state,
                        'up_down'   => $v->up_down
                    ]);
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
                        
                        LivePrice::where(['state' => $state , 'form' => $form , 'name' => $request->name])->whereDate( 'created_at' , $todayDate )->update(['cropYear'  => $request->cropYear[$state][$form], 'cropGrade' => $request->cropGrade[$state][$form],'min_price' => $price , 'max_price' => $request->max[$state][$form] , 'up_down' => $request->up_down[$state][$form] ]);
                    }
                }
                
            }else{
                foreach($request->min as $state => $values){
                    foreach($values as $form => $price){
                        $priceModel = new LivePrice();
                        $priceModel->name = $request->name;
                        $priceModel->form = $form;
                        $priceModel->min_price = $price;
                        $priceModel->cropYear  = $cropYear;
                        $priceModel->cropGrade = $cropGrade;
                        $priceModel->max_price = $request->max[$state][$form];
                        $priceModel->state = $state;
                        $priceModel->up_down = $request->up_down[$state][$form];
                        $priceModel->save();
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
            LivePrice::where('state' , $v)->update(['state_order' => $k]);
        }
        foreach($sortedNameData as $k => $v){
            LivePrice::where('name' , $v)->update(['name_order' => $k]);    
        }
        Session::flash('success','Success|Price saved successfully!');
        return back();
    }

    public function delete($id){
        LivePrice::find($id)->delete();
        Session::flash('success','Success|Record deleted successfully!');
        return back();
    }
}
