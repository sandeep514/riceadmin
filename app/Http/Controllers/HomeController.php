<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\ChatStatus;
use App\USD_defaultmaster;
use App\LivePrice;
use App\OceanFreight;
use App\QualityMaster;
use App\Defaultvalue;
use App\Events\AdminEvent;
use Session;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $defaultvalue = Defaultvalue::first();
        $chatstatus = ChatStatus::first();
        $defaultMaster = USD_defaultmaster::orderBy('applied_for' , 'DESC')->get();
        return view('home' , compact('chatstatus' , 'defaultMaster','defaultvalue'));
    }

    public function clonePreviousDayRecord()
    {
        $date = Carbon::now()->format('Y-m-d');
        $lastInsertedDataDate = LivePrice::orderBy('created_at' , 'desc')->whereDate('created_at' , '<=' , $date)->first()->created_at;

        $lastInsertedData = LivePrice::select("tradeFor","farmingType","name","form","cropGrade","cropYear","min_price","max_price","state","up_down","opening","closing","monthStart","monthEnd","status","state_order","name_order","form_order")->whereDate('created_at' , Carbon::parse($lastInsertedDataDate)->format('Y-m-d'))->get()->toArray();

        LivePrice::insert($lastInsertedData);
        Session::flash('success','Success|Price cloned successfully!');
        return back();
    }

    /**
     * Send a test Reverb notification to the React app (admin-events channel).
     */
    public function sendReverbNotification(Request $request)
    {
        $message = $request->input('message', 'Test notification from admin dashboard at ' . now()->format('H:i:s'));

        broadcast(new AdminEvent('admin_notification', [
            'message' => $message,
            'source' => 'admin_dashboard',
        ]))->toOthers();

        Session::flash('success', 'Success|Reverb notification sent. Check your React app.');
        return back();
    }
}
