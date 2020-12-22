<?php


namespace App\Repositories;


use App\City;
use App\CityZone;

class ZonesRepository
{
    public static function saveZone($request){
        $zoneModel = new CityZone;
        $zoneModel->fill($request->except(['city']));
        if($request->city == 0){
            $cityModel = self::createCity($request);
            $zoneModel->city = $cityModel->id;
        }else{
            $zoneModel->city = $request->city;
        }
        $zoneModel->save();
        return $zoneModel;
    }

    protected static function createCity($request){
        $cityModel = new City;
        $cityModel->city_name = $request->other_city;
        $cityModel->save();
        return $cityModel;
    }

    public static function updateZone($request, $id){
        $zoneModel = CityZone::find($id);
        $zoneModel->fill($request->except(['city']));
        if($request->city == 0){
            $cityModel = self::createCity($request);
            $zoneModel->city = $cityModel->id;
        }else{
            $zoneModel->city = $request->city;
        }
        $zoneModel->save();
        return $zoneModel;
    }

    public static function deleteZone($id){
        $zoneModel = CityZone::find($id);
        $zoneModel->delete();
        return $zoneModel;
    }
}
