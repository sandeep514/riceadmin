<?php

namespace App\Http\Controllers;

use App\DataTables\SamplesDataTable;
use App\Http\Requests\SampleRequest;
use App\Sample;
use App\Notification;
use App\Services\SampleService;
use Illuminate\Http\Request;
use Session;
use App\User;
use App\TrialPeriod;

class TrialPeriodController extends Controller
{
    public function index()
    {
        $trialPeriod = TrialPeriod::first();
        return View('trialPeriod.index' , compact('trialPeriod'));
    }
    public function save(Request $request)
    {
        $request->validate([
            'month' => 'required'    
        ]);
        $hasTrialPeriod = TrialPeriod::get()->count();
        if( $hasTrialPeriod > 0 ){
            TrialPeriod::where('id' , 1)->update(['month' => $request->month]);
        }else{
            TrialPeriod::create(['month' => $request->month]);    
        }
        
        return back();
    }
    
}