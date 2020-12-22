<?php


namespace App\Services;


use App\Repositories\CourierRepository;

class CourierService
{
    public static function saveCourier($request){
        return CourierRepository::saveCourier($request);
    }

    public static function updateCourier($request, $id){
        return CourierRepository::updateCourier($request, $id);
    }

    public static function deleteCourier($id){
        return CourierRepository::deleteCourier($id);
    }
}
