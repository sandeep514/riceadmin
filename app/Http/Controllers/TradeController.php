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
use App\RiceName;
use App\PublicPacking;
use App\TradeQueriesINR;
use App\TradeCurrentStatus;
use App\RiceFormMilestone3;
use App\WandModel;
use App\SellerPackingINR;
use App\Buyerpackinginr;
use App\WandTypeModel;
use App\TradeLike;

class TradeController extends Controller
{
    public function index(){
        $sellQueries = TradeQueriesINR::with(['RiceNameData' , 'RiceFormMilestone3','RicePackingBuyer' ,'RicePackingSeller','riceGrade' => function($query){
            return $query->with('getWandType');
        }])->orderBy('id' , 'DESC')->get();
        
        $tradeStatus = [1=> 'open' , 11=> 'close', 12 => 'hold'];
        $tradeCurrentStatus = TradeCurrentStatus::first();
        $currentTrade = $tradeStatus[$tradeCurrentStatus->id];

        return View('trade.index' , compact('sellQueries' , 'currentTrade'));

    }

    public function create(){
        // $qualityMaster = QualityMaster::pluck('quality_type_status' , 'quality_type');
        // dd($qualityMaster);        
        $qualityMaster = RiceName::pluck('type_status' , 'type');
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
        $isHotdeal = $request->hotdeal;

        if( isset($_FILES['packingImage']) ){
            $file_name      = $_FILES['packingImage']['name'];
            $file_size      = $_FILES['packingImage']['size'];
            $file_tmp       = $_FILES['packingImage']['tmp_name'];
            $file_type      = $_FILES['packingImage']['type'];

            move_uploaded_file($file_tmp,"uploads/".$file_name);
            $data['packing_file'] = $file_name;
        }


        foreach($_FILES["cookedFiles"]["tmp_name"] as $key=>$tmp_name) {
            $file_name=$_FILES["cookedFiles"]["name"][$key];
            $file_tmp=$_FILES["cookedFiles"]["tmp_name"][$key];

            move_uploaded_file($file_tmp,"uploads/".$file_name);
            if( $key == 0 ) {
                $data['cooked_file'] = $file_name;
            }else{
                $data['cooked_file'.$key] = $file_name;
            } 
        }

        foreach($_FILES["uncookedFiles"]["tmp_name"] as $key=>$tmp_name) {
            $file_name=$_FILES["uncookedFiles"]["name"][$key];
            $file_tmp=$_FILES["uncookedFiles"]["tmp_name"][$key];

            move_uploaded_file($file_tmp,"uploads/".$file_name);
            if( $key == 0 ) {
                $data['uncooked_file'] = $file_name;
            }else{
                $data['uncooked_file'.$key] = $file_name;
            } 
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
        $data['crop'] = $request->crop;
        $data['hotdeal'] = $isHotdeal;

        $data['moisture'] = $request->moisture;
        $data['kett'] = $request->kett;
        $data['broken'] = $request->broken;
        $data['dd'] = $request->dd;
        $data['admixture'] = $request->admixture;
        $data['elongation'] = $request->elongation;


        $tradeQuery = TradeQueriesINR::create($data);
        Session::flash('success','Success|Trade saved successfully!');
        if( $request->has('heart') ){
            for( $i = 1 ; $i <= $request->heart ;$i++ ){
                TradeLike::create([
                    'tradeId' => $tradeQuery->id,
                    'userId' => 224,   
                    'status' => 1
                ]);
            }
        }

        return back();
        return View('trade.index');
    }
    
    public function edit($id){
        $tradequeriesinr = TradeQueriesINR::where('id' , $id)->first();
        $tradeType = $tradequeriesinr->tradeType;
        $type = $tradequeriesinr->quality_type;
        $riceNameId = $tradequeriesinr->quality;
        $riceName = RiceName::orderBy('order', 'ASC')->where('status' , 1)->where('type_status' , $type)->pluck('id','name');
        $riceForm = RiceFormMilestone3::orderBy('order' , 'ASC')->pluck('id','name');
        $WandType = (WandTypeModel::pluck('id' , 'type'));
        $wandModel = WandModel::where('RiceNameId' , $riceNameId)->with(['getWandType'])->orderBy('order' , 'ASC')->get();

        if( $tradeType == 2 ){
            $packingType  = Buyerpackinginr::get();
        }else{
            $packingType  = SellerPackingINR::get();
        }
        $qualityMaster = RiceName::pluck('type_status' , 'type');
        // $packing = PublicPacking::get();

        return View('trade.edit' , compact('qualityMaster','tradequeriesinr','tradeType','type','riceNameId','riceName','riceForm','wandModel','packingType','WandType'));
    }
    
    public function update(Request $request){
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
        $isHotdeal = $request->hotdeal;

        if( $request->packingImage != '' && isset($_FILES['packingImage']) ){
            $file_name      = $_FILES['packingImage']['name'];
            $file_size      = $_FILES['packingImage']['size'];
            $file_tmp       = $_FILES['packingImage']['tmp_name'];
            $file_type      = $_FILES['packingImage']['type'];

            move_uploaded_file($file_tmp,"uploads/".$file_name);
            $data['packing_file'] = $file_name;
        }

        // if( $request->uncookedFiles != '' && isset($_FILES['uncookedFiles']) ){
        //     $file_name      = $_FILES['uncookedFiles']['name'];
        //     $file_size      = $_FILES['uncookedFiles']['size'];
        //     $file_tmp       = $_FILES['uncookedFiles']['tmp_name'];
        //     $file_type      = $_FILES['uncookedFiles']['type'];

        //     move_uploaded_file($file_tmp,"uploads/".$file_name);
        //     $data['uncooked_file'] = $file_name;
        // }
        
        // if( $request->cookedFiles != '' && isset($_FILES['cookedFiles']) ){
        //     $file_name      = $_FILES['cookedFiles']['name'];
        //     $file_size      = $_FILES['cookedFiles']['size'];
        //     $file_tmp       = $_FILES['cookedFiles']['tmp_name'];
        //     $file_type      = $_FILES['cookedFiles']['type'];

        //     move_uploaded_file($file_tmp,"uploads/".$file_name);
        //     $data['cooked_file'] = $file_name;
        // }
        foreach($_FILES["cookedFiles"]["tmp_name"] as $key=>$tmp_name) {
            $file_name=$_FILES["cookedFiles"]["name"][$key];
            $file_tmp=$_FILES["cookedFiles"]["tmp_name"][$key];

            move_uploaded_file($file_tmp,"uploads/".$file_name);
            if( $key == 0 ) {
                $data['cooked_file'] = $file_name;
            }else{
                $data['cooked_file'.$key] = $file_name;
            } 
        }

        foreach($_FILES["uncookedFiles"]["tmp_name"] as $key=>$tmp_name) {
            $file_name=$_FILES["uncookedFiles"]["name"][$key];
            $file_tmp=$_FILES["uncookedFiles"]["tmp_name"][$key];

            move_uploaded_file($file_tmp,"uploads/".$file_name);
            if( $key == 0 ) {
                $data['uncooked_file'] = $file_name;
            }else{
                $data['uncooked_file'.$key] = $file_name;
            } 
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
        $data['crop'] = $request->crop;
        $data['hotdeal'] = $isHotdeal;

        $data['moisture'] = $request->moisture;
        $data['kett'] = $request->kett;
        $data['broken'] = $request->broken;
        $data['dd'] = $request->dd;
        $data['admixture'] = $request->admixture;
        $data['elongation'] = $request->elongation;

        TradeQueriesINR::where('id' , $request['id'])->update(array_filter($data));
        Session::flash('success','Success|Trade saved successfully!');

        return back();
        return View('trade.index');
    }
    
    public function changeStatus ( $tradeId , $status ){
        TradeQueriesINR::where('id' , $tradeId)->update(['status' => $status]);
        // Session::flash('success','Status changed successfully!');
        
        return back();
    }

    public function updateTradeStatus($tradeStatus)
    {
        // 1: open
        // 11: close
        // 12: hold
        $status = [1=> 'open' , 11=> 'Market closed', 12 => 'Market on hold.'];

        TradeCurrentStatus::where('id' , 1)->update(['currentStatus' => $tradeStatus , 'message' => $status[$tradeStatus] ]);
        Session::flash('success','Success|Trade status updated successfully!');
        return back();
    }
}