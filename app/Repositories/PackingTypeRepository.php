<?php


namespace App\Repositories;


use App\PackingType;

class PackingTypeRepository
{
    public static function savePackingType($request){
        $packingTypeModel = new PackingType;
        $packingTypeModel->fill($request->all());
        $packingTypeModel->save();
        return $packingTypeModel;
    }

    public static function updatePackingType($request, $id){
        $packingTypeModel = PackingType::find($id);
        $packingTypeModel->fill($request->all());
        $packingTypeModel->save();
        return $packingTypeModel;
    }

    public static function deletePackingType($id){
        $packingTypeModel = PackingType::find($id);
        $packingTypeModel->delete();
        return $packingTypeModel;
    }
}
