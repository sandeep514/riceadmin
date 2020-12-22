<?php


namespace App\Repositories;


use App\Role;
use App\User;

class RoleRepository
{
    public static function saveRole($request){
        $roleModel = new Role();
        $roleModel->fill($request->all());
        $roleModel->save();
        return $roleModel;
    }


    public static function updateRole($request,$id){
        $roleModel = Role::find($id);
        $roleModel->fill($request->all());
        $roleModel->save();
        return $roleModel;
    }

    public static function deleteRole($id){
        $usersWithRole = User::whereRole($id)->get();
        if($usersWithRole->isEmpty()){
            $roleModel = Role::find($id);
            return $roleModel->delete();
        }else{
            return false;
        }
    }
}
