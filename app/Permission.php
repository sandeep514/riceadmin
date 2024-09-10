<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Permission extends Model
{
    protected $fillable = ['role_id	','module_id','designation','route_name','action','status'];

    public static function hasPermission($action){
        $user = Auth::user();
        $moduleSlug = request()->route()->getAction('module');
        $moduleId = Module::whereSlug($moduleSlug)->first();
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
