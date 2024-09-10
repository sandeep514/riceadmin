<?php


namespace App\Services;


use App\Repositories\PackingRepository;

class PackingService
{
    public static function savePacking($request){
        return PackingRepository::savePacking($request);
    }

    public static function updatePacking($request, $id){
        return PackingRepository::updatePacking($request, $id);
    }

    public static function deletePacking($id){
        return PackingRepository::deletePacking($id);
    }
}
