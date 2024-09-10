<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\ChatStatus;
use App\USD_defaultmaster;
use App\OceanFreight;
use App\QualityMaster;
use App\Defaultvalue;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $defaultvalue = Defaultvalue::first();
        $chatstatus = ChatStatus::first();
        $defaultMaster = USD_defaultmaster::orderBy('applied_for' , 'DESC')->get();
        return view('home' , compact('chatstatus' , 'defaultMaster','defaultvalue'));
    }
}