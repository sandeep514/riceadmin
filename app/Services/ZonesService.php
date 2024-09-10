<?php


namespace App\Services;


use App\Repositories\ZonesRepository;

class ZonesService
{
    public static function saveZone($request){
        return ZonesRepository::saveZone($request);
    }

    public static function updateZone($request, $id){
        return ZonesRepository::updateZone($request, $id);
    }

    public static function deleteZone($id){
        return ZonesRepository::deleteZone($id);
    }
}
