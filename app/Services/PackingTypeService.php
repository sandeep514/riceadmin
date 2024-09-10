<?php


namespace App\Services;


use App\Repositories\PackingTypeRepository;

class PackingTypeService
{
    public static function savePackingType($request){
        return PackingTypeRepository::savePackingType($request);
    }

    public static function updatePackingType($request, $id){
        return PackingTypeRepository::updatePackingType($request,$id);
    }

    public static function deletePackingType($id){
        return PackingTypeRepository::deletePackingType($id);
    }
}
