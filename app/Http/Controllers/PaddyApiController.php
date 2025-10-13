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

class PaddyApiController extends Controller
{
    public function listPaddy(Request $request)
    {
        // $todayDate = Carbon::now()->format('Y-m-d');
        $lastAddedRow = PaddyPrice::orderBy('created_at' , 'desc')->first();
        $selectedStatesIds = [];
        if( $lastAddedRow ){
            $lastAddedDate = Carbon::parse($lastAddedRow->created_at)->format('Y-m-d');
            $selectedStatesIds = array_unique(PaddyPrice::whereDate('created_at' , $lastAddedDate)->get()->pluck('state')->toArray());
        }

        $paddyState = PaddyStateModel::select('id' , 'state')->where('status' , 1)->whereIn('id' , $selectedStatesIds)->get();
        return response()->json(['status' => true , 'message' => 'Paddy state successfully' , 'data' => $paddyState]);
    }

    public function listPaddyMandi($stateId)
    {
        $lastAddedRow = PaddyPrice::orderBy('created_at' , 'desc')->first();
        $selectedMandiIds = [];
        if( $lastAddedRow ){
            $lastAddedDate = Carbon::parse($lastAddedRow->created_at)->format('Y-m-d');
            ;
            $selectedMandiIds = array_unique(PaddyPrice::where('state' , $stateId)->whereDate('created_at' , $lastAddedDate)->get()->pluck('mandi')->toArray());
        }
        $paddyMandi = PaddyMandiModel::select('id' ,'mandi' ,'state_id')->where('state_id' , $stateId)->whereIn('id' , $selectedMandiIds)->where('status' , 1)->get();
        return response()->json(['status' => true , 'message' => 'Paddy mandi successfully' , 'data' => $paddyMandi]);
    }
    public function getPaddyPrices($mandi_id , $state_id)
    {   
        $lastEnterRow = PaddyPrice::orderBy('created_at' , 'desc')->first();

        if( $lastEnterRow ){
            $lastEnterDate = $lastEnterRow->created_at->format('Y-m-d');

            $paddyPrices = PaddyPrice::where('mandi', $mandi_id)
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
        
        return response()->json(['status' => true , 'message' => 'Paddy get successfully' , 'data' => $paddyPrices]);
    }
}