<?php


namespace App\Services;


use App\Repositories\SampleRepository;

class SampleService
{
    public static function saveSample($request){
        return SampleRepository::saveSample($request);
    }

    public static function updateSample($request,$id){
        return SampleRepository::updateSample($request,$id);
    }

    public static function deleteSample($id){
        return SampleRepository::deleteSample($id);
    }
}
