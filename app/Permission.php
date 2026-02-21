<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Permission extends Model
{
    protected $fillable = ['role_id	','module_id','designation','route_name','action','status'];

    public static function hasPermission($action){
        $user = Auth::user();
        
        // ✅ Admin (role 2) has all permissions - bypass check
        if($user->role == 2){
            return true;
        }
        
        // Check if route has module action
        $route = request()->route();
        if(!$route){
            return false;
        }
        
        $moduleSlug = $route->getAction('module');
        if(!$moduleSlug){
            return false;
        }
        
        $moduleId = Module::whereSlug($moduleSlug)->first();
        if(!$moduleId){
            return false;
        }
        
        $where = ['module_id'=>$moduleId->id,'action'=>$action,'role_id'=>$user->role,'status'=>1];
        if($user->role == 3){
            $where['designation'] = $user->field_runner_rel->designation;
        }
        $hasPermission = self::where($where)->first();
        if($hasPermission != null){
            return true;
        }else{
            return false;
        }
    }
}
