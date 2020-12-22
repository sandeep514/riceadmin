<?php

namespace App\Http\Controllers;

use Session;
use App\Port;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PortsController extends Controller
{
    public function index(Request $request){
        if($request->has('date')){
            $todayPrice = Port::where(DB::raw('date(created_at)'),$request->date)->get();
        }else{
            $lastUpdatedDate = Port::orderBy('created_at' , 'DESC')->first();
            $dateCreate = date_create($lastUpdatedDate->created_at);
            $formatedDate = date_format($dateCreate , 'Y/m/d');
            
            $todayPrice = Port::whereDate('created_at' , $formatedDate)->get();
        }
        return view('ports.create',['prices'=>$todayPrice]);
    }

    public function save(Request $request){
        $requestData = $request->except(['_token','date']);
        foreach($requestData as $state => $routes){
            foreach($routes as $route => $price){
                if( $price != null || $price != '' ){
                    $isPortPriceAlreadyExists = Port::where(DB::raw('date(created_at)'),Carbon::today()->format('Y-m-d'))
                    ->firstOrNew(['state'=>$state,'route'=>$route]);
                    $isPortPriceAlreadyExists->state = $state;
                    $isPortPriceAlreadyExists->route = $route;
                    $isPortPriceAlreadyExists->price = $price;
                    $isPortPriceAlreadyExists->save();
                }
                
            }
        }
        Session::flash('success','Success|Ports saved successfully!');
        return back();
    }
}
