<?php


namespace App\Services;


use App\Repositories\RoleRepository;

class RoleService
{
    public static function saveRole($request){
        return RoleRepository::saveRole($request);
    }

    public static function updateRole($request,$id){
        return RoleRepository::updateRole($request,$id);
    }

    public static function deleteRole($id){
        return RoleRepository::deleteRole($id);
    }
}
