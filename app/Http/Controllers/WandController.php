<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\WandTypeModel;
use App\WandModel;
use App\RiceName;
use App\QualityMaster;
use Session;

class WandController extends Controller
{
    public function index(){
        $data = QualityMaster::groupBy('quality')->get()->pluck('id','quality' );
        return View('wand.index' , compact('data'));
    }

    public function create($riceFormId){
        $riceFormDecodeId = base64_decode($riceFormId);

        $WandModelData = WandModel::where('RiceNameId' , $riceFormDecodeId )->pluck('value' , 'wandTypeId')->toArray();
        $wandTypeModal = WandTypeModel::orderBy('order', 'ASC')->get();
        $riceName = RiceName::distinct('name')->get();

        return View('wand.create' , compact('wandTypeModal' , 'riceName','WandModelData'));
    }

    public function save(Request $request){
        $wandType = $request->wandType;
        $wandValues = $request->wandValue;

        $wandData = $request->wandValue;
        $filterWandData = array_filter($wandData);
        foreach($filterWandData as $k => $v){
            $checkIfHasWand = WandModel::where(['RiceNameId' => $wandType,'wandTypeId' => $k])->first();
            if( $checkIfHasWand != null && $checkIfHasWand != '' ){
                WandModel::where(['RiceNameId' => $wandType,'wandTypeId' => $k])->update(['value' => $v,'status' => 1]);
            }else{
                WandModel::create(['RiceNameId' => $wandType,'wandTypeId' => $k,'value' => $v,'status' => 1]);
            }
            
        }
        Session::flash('sucess' , 'Data added successfully');
        return back();
    }

    public function edit(){
        dd('edit');
    }

    public function update(){
        dd('update');
    }

    public function changeStatus(){
        dd('changeStatus');
    }

}