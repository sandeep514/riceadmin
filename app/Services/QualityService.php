<?php


namespace App\Services;


use App\Repositories\QualityRepository;

class QualityService
{
    public static function saveQuality($request){
        return QualityRepository::saveQuality($request);
    }

    public static function updateQuality($request, $id){
        return QualityRepository::updateQuality($request, $id);
    }

    public static function deleteQuality($id){
        return QualityRepository::deleteQuality($id);
    }

    public static function importQuality($request){
        return QualityRepository::importQuality($request);
    }
}
