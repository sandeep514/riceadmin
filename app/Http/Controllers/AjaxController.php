<?php

namespace App\Http\Controllers;

use App\City;
use App\SampleLabReport;
use App\Services\SampleRegisterService;
use Illuminate\Http\Request;

class AjaxController extends Controller
{
    public function saveCity(Request $request){

        City::max('order');
        $cityModel = City::firstOrNew(['city_name'=>$request->city]);
        
        if($cityModel->exists){
            return response()->json(['status'=>'error','message'=>'City already exists!']);
        }else{
            $cityModel->city_name = $request->city;
            $cityModel->save();
            $cities = City::userCities();
            return response()->json(['status'=>'success','cities'=>$cities]);
        }

    }

    public function getSampleRegisterFromSntc($sntcNo, $type = 'json'){
        $sampleRegisterModel = SampleRegisterService::getDetailsBySntcNo($sntcNo);
        $qualityRel = $sampleRegisterModel->quality_rel;
        $quality = ucwords(str_replace('-',' ',$qualityRel->nameRel->type))
            .' - '.$qualityRel->nameRel->name.' '.$qualityRel->formRel->form_name.' ('.$qualityRel->typeRel->name.')';
        if($type == 'json'){
            return response()->json([
                'quality'=>$sampleRegisterModel->quality,
                'packing_type'=>$sampleRegisterModel->packing_type,
                'quality_text'=>$quality,
                'packing' => $sampleRegisterModel->packing_rel->code,
                'packing_type_details' => $sampleRegisterModel->packing_type_rel->name,
                'seller' => $sampleRegisterModel->supplier_rel->name,
                'no_of_bags' => $sampleRegisterModel->no_of_bags,
                'qty_per_bag' => $sampleRegisterModel->qty_per_bag
            ]);
        }else{
            return [
                'quality'=>$sampleRegisterModel->quality,
                'packing_type'=>$sampleRegisterModel->packing_type,
                'quality_text'=>$quality,
                'packing' => $sampleRegisterModel->packing_rel->code,
                'packing_type_details' => $sampleRegisterModel->packing_type_rel->name,
                'seller' => $sampleRegisterModel->supplier_rel->name,
                'no_of_bags' => $sampleRegisterModel->no_of_bags,
                'qty_per_bag' => $sampleRegisterModel->qty_per_bag
            ];
        }
    }

    public function getDataForDealLabReport($sntcNo){
        $sampleDetails = $this->getSampleRegisterFromSntc($sntcNo,'array');
        $sampleLabReportData = SampleLabReport::where(['sntc_no'=>$sntcNo])->first();
        if($sampleLabReportData != null){
            $sampleDetails['lab_reports'] = $sampleLabReportData->toArray();
        }else{
            $sampleDetails['lab_reports'] = null;
        }
        return response()->json($sampleDetails);
    }
}
