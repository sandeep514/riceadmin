<?php


namespace App\Services;


use App\Repositories\UserRepository;

class UserService
{
    public static function saveUser($request){
        return UserRepository::saveUser($request);
    }

    public static function updateUser($request, $id){
        return UserRepository::updateUser($request, $id);
    }
}
