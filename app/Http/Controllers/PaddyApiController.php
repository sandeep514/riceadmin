<?php

namespace App\Http\Controllers;

use App\Helpers\StatusChat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\MailController;
use Illuminate\Support\Str;
use App\PaddyStateModel;
use App\PaddyMandiModel;
use App\PaddyPrice;
use Mail;
use Auth;
use App\RiceName;

class PaddyApiController extends Controller
{
    public function listPaddy(Request $request)
    {
        // $todayDate = Carbon::now()->format('Y-m-d');
        $lastAddedRow = PaddyPrice::orderBy('created_at' , 'desc')->where(function($q){
            return $q->where('hand_cutting_price' ,'!=', 0 )->orWhere('machine_cutting_price' ,'!=', 0);
        })->first();

        $selectedStatesIds = [];
        if( $lastAddedRow ){
            $lastAddedDate = Carbon::parse($lastAddedRow->created_at)->format('Y-m-d');
            
            $selectedStatesIds = array_unique(PaddyPrice::whereDate('created_at' , $lastAddedDate)->where(function($q){
                    return $q->where('hand_cutting_price' ,'!=', 0 )->orWhere('machine_cutting_price' ,'!=', 0);
                })->pluck('state')->toArray());
        }

        $paddyState = PaddyStateModel::select('id' , 'state')->where('status' , 1)->whereIn('id' , $selectedStatesIds)->get();
        return response()->json(['status' => true , 'message' => 'Paddy state successfully' , 'data' => $paddyState]);
    }

    public function listPaddyMandi($stateId)
    {
        $lastAddedRow = PaddyPrice::orderBy('created_at' , 'desc')->where(function($q){
                    return $q->where('hand_cutting_price' ,'!=', 0 )->orWhere('machine_cutting_price' ,'!=', 0);
                })->first();
        $selectedMandiIds = [];
        if( $lastAddedRow ){
            $lastAddedDate = Carbon::parse($lastAddedRow->created_at)->format('Y-m-d');
            ;
            $selectedMandiIds = array_unique(PaddyPrice::where('state' , $stateId)->whereDate('created_at' , $lastAddedDate)->where(function($q){
                    return $q->where('hand_cutting_price' ,'!=', 0 )->orWhere('machine_cutting_price' ,'!=', 0);
                })->pluck('mandi')->toArray());
        }
        $paddyMandi = PaddyMandiModel::select('id' ,'mandi' ,'state_id')->where('state_id' , $stateId)->whereIn('id' , $selectedMandiIds)->where('status' , 1)->get();
        return response()->json(['status' => true , 'message' => 'Paddy mandi successfully' , 'data' => $paddyMandi]);
    }

    public function getPaddyPrices($mandi_id , $state_id)
    {   
        $lastEnterRow = PaddyPrice::orderBy('created_at' , 'desc')->where(function($q){
                    return $q->where('hand_cutting_price' ,'!=', 0 )->orWhere('machine_cutting_price' ,'!=', 0);
                })->first();

        $paddyPrices = collect();
        $lastCreated_at = '';
        if( $lastEnterRow ){
            $lastCreated_at = $lastEnterRow->created_at->format('Y-m-d H:i');
            $lastEnterDate = $lastEnterRow->created_at->format('Y-m-d');

            $paddyPrices = PaddyPrice::where('mandi', $mandi_id)
                ->where(function($q){
                    return $q->where('hand_cutting_price' ,'!=', 0 )->orWhere('machine_cutting_price' ,'!=', 0);
                })
                ->where('state', $state_id)
                ->whereDate('created_at' , $lastEnterDate)
                ->with(['getMandi_rel:id,mandi','getState_rel:id,state','quality_rel:id,name'])
                ->orderBy('id', 'DESC')
                ->get()
                ->groupBy(function ($item) {
                    return $item->created_at->format('Y-m-d');
                })
                ->map(function ($group) {
                    return $group->groupBy('quality_id')->map(function ($qGroup) {
                        return $qGroup->first();
                    });
                })
            ->first();
        }
        
        return response()->json(['status' => true , 'message' => 'Paddy get successfully' , 'data' => $paddyPrices,'lastUpdatedDate' => $lastCreated_at]);
    }

    public function getPaddyPricesByPaddy($stateId , $paddyId)
    {
        $lastEnterRow = PaddyPrice::orderBy('created_at' , 'desc')->where(function($q){
                    return $q->where('hand_cutting_price' ,'!=', 0 )->orWhere('machine_cutting_price' ,'!=', 0);
                })->first();

        $paddyPrices = collect();
        $lastCreated_at = '';
        if( $lastEnterRow ){
            $lastCreated_at = $lastEnterRow->created_at->format('Y-m-d H:i');
            $lastEnterDate = $lastEnterRow->created_at->format('Y-m-d');
            // dd($lastEnterDate);
            $paddyPrices = PaddyPrice::where('quality_id', $paddyId)
                ->where(function($q){
                    return $q->where('hand_cutting_price' ,'!=', 0 )->orWhere('machine_cutting_price' ,'!=', 0);
                })
                ->where('state', $stateId)
                ->whereDate('created_at' , $lastEnterDate)
                ->with(['getMandi_rel:id,mandi','getState_rel:id,state','quality_rel:id,name'])
                ->orderBy('id', 'DESC')
                ->get()
                ->groupBy(function ($item) {
                    return $item->created_at->format('Y-m-d');
                })
                ->map(function ($group) {
                    return $group->groupBy('quality_id')->map(function ($qGroup) {
                        return $qGroup;
                    });
                })
            ->first();
        }
        return response()->json(['status' => true , 'message' => 'Paddy get successfully' , 'data' => $paddyPrices,'lastUpdatedDate' => $lastCreated_at]);
    }

    public function getPaddyQualities($stateId)
    {
        $lastEnterRow = PaddyPrice::orderBy('created_at' , 'desc')->where(function($q){
            return $q->where('hand_cutting_price' ,'!=', 0 )->orWhere('machine_cutting_price' ,'!=', 0);
        })->first();
        $states = collect();

        if( $lastEnterRow ){

            $lastEnterDate = $lastEnterRow->created_at->format('Y-m-d');
            $paddyQualityIds = PaddyPrice::where('state', $stateId)
                ->whereDate('created_at' , $lastEnterDate)
                ->orderBy('id', 'DESC')
                ->pluck('quality_id')->toArray();

            $paddyQualityArrayIds = array_values(array_unique($paddyQualityIds));

            $qualities = RiceName::select('id','name')->whereIn('id' , $paddyQualityArrayIds)->get();
        }
        return response()->json(['status' => true , 'message' => 'Paddy mandi get successfully' , 'data' => $qualities]);
    }


    public function GetPaddyMapData(Request $request)
    {
        $mandi_id = ($request->mandi_id);
        $state_id = ($request->state_id);
        $quality_id = ($request->quality_id);
        // $crop_id = base64_decode($request->crop_id);

        $paddyPricePre = PaddyPrice::where(['mandi'  => $mandi_id , 'state' => $state_id, 'quality_id' => $quality_id ]);

        $hand_cutting_price = (clone $paddyPricePre)->where( 'hand_cutting_price','!=', '----')->pluck('hand_cutting_price' , 'created_at')->map(function($q){
            return (int)(( str_contains($q , '-') ) ? explode('-' , $q)[1] : $q);
        });

        $machine_cutting_price = (clone $paddyPricePre)->where( 'machine_cutting_price','!=', '----')->pluck('machine_cutting_price' , 'created_at')->map(function($q){
            return (int)(( str_contains($q , '-') ) ? explode('-' , $q)[1] : $q);
        });

        return response()->json(['status' => true , 'message' => 'Paddy get successfully' , 'data' => ['hand_cutting_price' => $hand_cutting_price , 'machine_cutting_price' => $machine_cutting_price]]);

    }



}