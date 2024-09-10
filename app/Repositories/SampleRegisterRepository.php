<?php


namespace App\Repositories;


use App\SampleRegister;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SampleRegisterRepository
{
    public static function saveSampleRegister($request){
        $sampleRegisterModel = new SampleRegister;
        return self::storeSampleRegister($sampleRegisterModel,$request,'new');
    }

    public static function updateSampleRegister($request, $id){
        $sampleRegisterModel = SampleRegister::find($id);
        return self::storeSampleRegister($sampleRegisterModel,$request,'update');
    }

    protected static function storeSampleRegister($sampleRegisterModel, $request, $type){
        $sampleRegisterModel->fill($request->except(['photo','date','sntc_no']));
        if($request->hasFile('photo')){
            $fileExtension = $request->file('photo')->getClientOriginalExtension();
            $randomName = Str::random(10);
            $request->file('photo')->move('sample-register-images',$randomName.'.'.$fileExtension);
            $sampleRegisterModel->photo = $randomName.'.'.$fileExtension;
        }
        $sampleRegisterModel->date = Carbon::parse($request->date)->format('Y-m-d');
        if($type == 'new'){
            $sampleRegisterModel->sntc_no = self::getMaxSntc();
        }
        $sampleRegisterModel->save();
        return $sampleRegisterModel;
    }

    public static function deleteSampleRegister($id){
        $sampleRegisterModel = SampleRegister::find($id);
        return $sampleRegisterModel->delete();
    }

    public static function getMaxSntc(){
        $maxSNTC = SampleRegister::orderBy('id','desc')->first();
        if($maxSNTC == null){
            return str_pad(1, 5,0,STR_PAD_LEFT);
        }else{
            return str_pad($maxSNTC->sntc_no + 1, 5,0,STR_PAD_LEFT);
        }
    }

    public static function getDetailsBySntcNo($sntcNo){
        return SampleRegister::with(['quality_rel','supplier_rel','packing_rel','packing_type_rel'])
            ->where(['sntc_no'=>$sntcNo])->first();
    }
}
