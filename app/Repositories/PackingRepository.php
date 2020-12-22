<?php


namespace App\Repositories;


use App\Packing;

class PackingRepository
{
    public static function savePacking($request){
        $packingModel = new Packing;
        $packingModel->fill($request->all());
        $packingModel->save();
        return $packingModel;
    }

    public static function updatePacking($request, $id){
        $packingModel = Packing::find($id);
        $packingModel->fill($request->all());
        $packingModel->save();
        return $packingModel;
    }

    public static function deletePacking($id){
        $packingModel = Packing::find($id);
        $packingModel->delete();
        return $packingModel;
    }
}
