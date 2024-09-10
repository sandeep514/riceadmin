<?php


namespace App\Services;


use App\Repositories\SampleOutwardRepository;

class SampleOutwardService
{
    public static function saveSampleOutward($request){
        return SampleOutwardRepository::saveSampleOutward($request);
    }

    public static function updateSampleOutward($request, $id){
        return SampleOutwardRepository::updateSampleOutward($request,$id);
    }

    public static function deleteSampleOutward($id){
        return SampleOutwardRepository::deleteSampleOutward($id);
    }
}
