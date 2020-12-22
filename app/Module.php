<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Module extends Model
{
    protected $fillable = ['name','icon','slug','sort_order','status'];

    public static function modulesList(){
        $user = Auth::user();

        $where = ['role_id'=>Auth::user()->role,'status'=>1];
        if($user->role == 3){
            $where['designation'] = $user->field_runner_rel->designation;
        }
        $permissionModel = Permission::where($where)->get();

        //Modules according to role
        $modulesEnabledInRole = collect(json_decode($user->role_rel->modules,true));

        $modulesEnabledInRole = $modulesEnabledInRole->filter(function($module){
            if($module == 'on'){
                return $module;
            }
        });
        $modules = self::with(['permissions'])
            ->whereIn('id',$permissionModel->unique('module_id')->groupBy('module_id')->keys())
            ->whereIn('slug',$modulesEnabledInRole->keys())
            ->get();
        return $modules;
    }

    public function permissions(){
        return $this->hasMany(Permission::class,'module_id','id')->where('status',1);
    }
}
