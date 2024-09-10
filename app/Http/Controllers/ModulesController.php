<?php

namespace App\Http\Controllers;

use App\Module;
use App\Permission;
use App\Role;
use Illuminate\Http\Request;
use Session;

class ModulesController extends Controller
{
    public function index($role = null){
        $modules = Module::get();
        $activeModules = [];
        $roles = Role::pluck('role_name','id');
        if($role != null){
            $activeModules = Role::find($role)->modules;
            if($activeModules != ''){
                $activeModules = json_decode($activeModules,true);
            }else{
                $activeModules = [];
            }
        }
        return view('modules.index',['modules'=>$modules,'roles'=>$roles,'activeModules'=>$activeModules]);
    }

    public function permissions($module_id,$role_id){
        $module = Module::find($module_id);
        $routes = \Route::getRoutes();
        $routesName = [];
        foreach($routes->getRoutes() as $key => $route) {
            if(@$route->action['module'] != null) {
                if($route->action['module'] == $module->slug){
                    if($route->methods[0] == 'GET' || $route->methods[0] == 'DELETE'){
                        $routesName[$route->getAction('action')] = $route->getAction('as');
                    }
                }
            }
        }
        $permissions = Permission::where(['role_id'=>$role_id,'module_id'=>$module_id])->get();
        return view('modules.permissions',['routes'=>$routesName,'permissions'=>$permissions]);
    }

    public function saveModule(Request $request){
        Role::where(['id'=>$request->role])->update(['modules'=>json_encode($request->enable_disable)]);
        Session::flash('success','Success|Modules updated successfully!');
        return back();
    }

    public function savePermissions(Request $request){
        if($request->role_id == 3){
            $request->validate([
                'designation' => 'required'
            ]);
        }
        foreach($request->permissions as $action => $permission){
            $permissionModel = Permission::firstOrNew(['role_id'=>$request->role_id,
                'module_id'=>$request->module_id,'action'=>$action]);
            $permissionModel->module_id = $request->module_id;
            $permissionModel->role_id = $request->role_id;
            $permissionModel->action = $action;
            if($request->role_id == 3) {
                $permissionModel->designation = $request->designation;
            }
            $routeName = array_keys($permission)[0];
            $permissionModel->route_name = $routeName;
            if($permission[$routeName] == 'off'){
                $permissionModel->status = 0;
            }else{
                $permissionModel->status = 1;
            }
            $permissionModel->save();
        }
        Session::flash('success','Success|Permissions saved successfully!');
        return back();
    }
}
