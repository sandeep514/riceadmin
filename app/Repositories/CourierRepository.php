<?php


namespace App\Repositories;


use App\Courier;
use App\Sample;
use Carbon\Carbon;

class CourierRepository
{
    public static function saveCourier($request){
        $courierModel = new Courier;
        $courierModel->fill($request->except(['sample','date']));
        $courierModel->date = Carbon::parse($request->date)->format('Y-m-d');
        $courierModel->samples = json_encode($request->sample);
        $courierModel->save();
        self::updateSamples($request,$courierModel);
        return $courierModel;
    }


    public static function updateSamples($request,$courierModel){
        foreach($request->sample as $sampleId => $status){
            $sampleModel = Sample::find($sampleId);
            $sampleModel->courier_status = 1;
            $sampleModel->courier_id = $courierModel->id;
            $sampleModel->save();
        }
    }

    public static function updateCourier($request, $id){
        $courierModel = Courier::find($id);
        $courierModel->fill($request->except(['sample','date']));
        $courierModel->date = Carbon::parse($request->date)->format('Y-m-d');
        $courierModel->samples = json_encode($request->sample);
        $courierModel->save();
        self::updateSamples($request,$courierModel);
        return $courierModel;
    }

    public static function deleteCourier($id){
        return Courier::find($id)->delete();
    }
}
