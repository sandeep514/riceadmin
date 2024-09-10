<?php


namespace App\Services;


use App\Repositories\SampleRegisterRepository;

class SampleRegisterService
{
    public static function saveSampleRegister($request){
        return SampleRegisterRepository::saveSampleRegister($request);
    }

    public static function updateSampleRegister($request,$id){
        return SampleRegisterRepository::updateSampleRegister($request,$id);
    }

    public static function deleteSampleRegister($id){
        return SampleRegisterRepository::deleteSampleRegister($id);
    }

    public static function getMaxSntc(){
        return SampleRegisterRepository::getMaxSntc();
    }

    public static function getDetailsBySntcNo($sntcNo){
        return SampleRegisterRepository::getDetailsBySntcNo($sntcNo);
    }
}
