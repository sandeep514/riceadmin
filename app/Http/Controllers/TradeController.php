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
use App\QualityMaster;
use App\PublicPacking;
use App\TradeQueriesINR;

class TradeController extends Controller
{
    public function index(){
        $sellQueries = TradeQueriesINR::get();
        return View('trade.index' , compact('sellQueries'));
    }
    public function create(){
        $qualityMaster = QualityMaster::pluck('quality_type_status' , 'quality_type');
        $packing = PublicPacking::get();
        return View('trade.create' , compact('qualityMaster','packing'));
    }
    public function save(Request $request){

        $data = [];

        $selectedQualityTypeInt = $request->category;
        $quality = $request->quality;
        $qualityForm = $request->riceform;
        $selectedGrade = $request->ricegrade;
        $changePackingType = $request->ricepacking;
        $quantity = $request->quantity;
        $offerPrice = $request->price;
        $validDays = $request->validity;
        $additioanlInfo = $request->additioanlInfo;
        $location = $request->location;
        $tradeType = $request->tradeType;
        
        if( isset($_FILES['packingImage']) ){
            $file_name      = $_FILES['packingImage']['name'];
            $file_size      = $_FILES['packingImage']['size'];
            $file_tmp       = $_FILES['packingImage']['tmp_name'];
            $file_type      = $_FILES['packingImage']['type'];

            move_uploaded_file($file_tmp,"uploads/".$file_name);
            $data['packing_file'] = $file_name;
        }

        if( isset($_FILES['uncookedFiles']) ){
            $file_name      = $_FILES['uncookedFiles']['name'];
            $file_size      = $_FILES['uncookedFiles']['size'];
            $file_tmp       = $_FILES['uncookedFiles']['tmp_name'];
            $file_type      = $_FILES['uncookedFiles']['type'];

            move_uploaded_file($file_tmp,"uploads/".$file_name);
            $data['uncooked_file'] = $file_name;
        }
        
        if( isset($_FILES['cookedFiles']) ){
            $file_name      = $_FILES['cookedFiles']['name'];
            $file_size      = $_FILES['cookedFiles']['size'];
            $file_tmp       = $_FILES['cookedFiles']['tmp_name'];
            $file_type      = $_FILES['cookedFiles']['type'];

            move_uploaded_file($file_tmp,"uploads/".$file_name);
            $data['cooked_file'] = $file_name;
        }

        $data['quality_type'] = $selectedQualityTypeInt;
        $data['quality'] = $quality;
        $data['qualityForm'] = $qualityForm;
        $data['grade'] = $selectedGrade;
        $data['packing'] = $changePackingType;
        $data['quantity'] = $quantity;
        $data['offerPrice'] = $offerPrice;
        $data['validDays'] = $validDays;
        $data['additioanlInfo'] = $additioanlInfo;
        $data['location'] = $location;
        $data['tradeType'] = $tradeType;


        TradeQueriesINR::create($data);
        Session::flash('success','Success|Trade saved successfully!');

        return back();




        dd($request->all());
        return View('trade.index');
    }
    public function edit(){
        return View('trade.edit');
    }
    public function update(){
        return View('trade.index');
    }
    public function changeStatus ( $tradeId , $status ){
        TradeQueriesINR::where('id' , $tradeId)->update(['status' => $status]);
        // Session::flash('success','Status changed successfully!');
        
        return back();

    }
}