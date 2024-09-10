<?php


namespace App\Repositories;


use App\SampleOutward;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SampleOutwardRepository
{
    public static function saveSampleOutward($request){
        $sampleOutwardModel = new SampleOutward;
        return self::storeSampleOutward($sampleOutwardModel,$request);
    }


    protected static function storeSampleOutward($sampleOutwardModel,$request){
        $sampleOutwardModel->fill($request->except(['photo','date']));
        if($request->hasFile('photo')){
            $fileExtension = $request->file('photo')->getClientOriginalExtension();
            $randomName = Str::random(10);
            $request->file('photo')->move('sample-register-images',$randomName.'.'.$fileExtension);
            $sampleOutwardModel->photo = $randomName.'.'.$fileExtension;
        }
        $sampleOutwardModel->date = Carbon::parse($request->date)->format('Y-m-d');
        $sampleOutwardModel->save();
        return $sampleOutwardModel;
    }

    public static function updateSampleOutward($request, $id){
        $sampleOutwardModel = SampleOutward::find($id);
        return self::storeSampleOutward($sampleOutwardModel,$request);
    }

    public static function deleteSampleOutward($id){
        return SampleOutward::find($id)->delete();
    }
}
