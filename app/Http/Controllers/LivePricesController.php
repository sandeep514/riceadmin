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

        $RiceForm= RiceForm::get();
        $RiceName= RiceName::get();
        $livePrice = LivePrice::get()->groupBy('state');

        $riceModel = null;
        $riceForms = null;
        $todaysPrices = null;
        $lastPrices = null;
        if($riceName != null){
            $riceModel = RiceName::find($riceName);
            $riceForms = RiceForm::where(['type'=>$riceModel->type])->get();
            if( LivePrice::get()->count() == 0 ){
                return view('live_prices.create',['prices'=>[],'riceModel'=>$riceModel,'riceForm'=>$riceForms,'today_price'=>$todaysPrices,'lastPrices' => $lastPrices]);
            }
            $todaysPrices = LivePrice::where(['name'=>$riceName])->where(DB::raw('date(created_at)'),Carbon::now()->format('Y-m-d'))->get();
            $lastAvaibleRecord = LivePrice::orderBy('created_at' , "DESC")->first();
            
            $lastAvailableDate = date_format(date_create($lastAvaibleRecord->created_at) , 'Y-m-d');
            $lastPrices = LivePrice::where(['name'=>$riceName])->with(['form_rel','name_rel'])->whereDate('created_at' , $lastAvailableDate)->get();
        }

        if($request->has('date')){
            $prices = LivePrice::with(['name_rel','form_rel'])->where('min_price' ,'!=', 0)->where('max_price' ,'!=', 0)->where(DB::raw('date(created_at)'),Carbon::parse($request->date)->format('Y-m-d'))->get();
        }else{
            $prices = LivePrice::with(['name_rel','form_rel'])->where('min_price' ,'!=', 0)->where('max_price' ,'!=', 0)->where(DB::raw('date(created_at)'),Carbon::now()->format('Y-m-d'))->get();
        }
        return view('live_prices.create',['livePrice'=>$livePrice,'prices'=>$prices,'riceModel'=>$riceModel,'riceForm'=>$riceForms,'today_price'=>$todaysPrices,'lastPrices' => $lastPrices]);
    }

    public function savePrice(Request $request){
        $todayDate = Carbon::now()->format('Y-m-d');
        $lastAvailableDate ='';
        $lastAvaibleRecord = LivePrice::orderBy('created_at' , "DESC")->first();

        if( $lastAvaibleRecord != null ){
            $lastAvailableDate = date_format(date_create($lastAvaibleRecord->created_at) , 'Y-m-d');    
        }

        if( $todayDate == $lastAvailableDate ){
            foreach($request->min as $state => $values){
                foreach($values as $form => $price){
                    $userDetails = LivePrice::where(['state' => $state , 'form' => $form , 'name' => $request->name])->whereDate( 'created_at' , $todayDate )->first();

                    if( $userDetails ){
                        LivePrice::where(['state' => $state , 'form' => $form , 'name' => $request->name])->whereDate( 'created_at' , $todayDate )->update([ 'min_price' => $price , 'max_price' => $request->max[$state][$form] , 'up_down' => $request->up_down[$state][$form] ]);    
                    }else{
                        LivePrice::create([
                            'name'      => $request->name,
                            'form'      => $form,
                            'min_price' => $price, 
                            'max_price' => $request->max[$state][$form],
                            'state'     => $state,
                            'up_down'   => $request->up_down[$state][$form]
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
                        
                        LivePrice::where(['state' => $state , 'form' => $form , 'name' => $request->name])->whereDate( 'created_at' , $todayDate )->update([ 'min_price' => $price , 'max_price' => $request->max[$state][$form] , 'up_down' => $request->up_down[$state][$form] ]);
                    }
                }
                
            }else{
                foreach($request->min as $state => $values){
                    foreach($values as $form => $price){
                        $priceModel = new LivePrice();
                        $priceModel->name = $request->name;
                        $priceModel->form = $form;
                        $priceModel->min_price = $price;
                        $priceModel->max_price = $request->max[$state][$form];
                        $priceModel->state = $state;
                        $priceModel->up_down = $request->up_down[$state][$form];
                        $priceModel->save();
                    }
                }
                
                
                
            }
           
            
        }
        
        
        
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
        Session::flash('success','Success|Price saved successfully!');
        return back();
    }

    public function delete($id){
        LivePrice::find($id)->delete();
        Session::flash('success','Success|Record deleted successfully!');
        return back();
    }
}
