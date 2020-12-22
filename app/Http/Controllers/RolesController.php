<?php

namespace App\Http\Controllers;

use App\DataTables\RolesDataTable;
use App\Http\Requests\RoleRequest;
use App\Role;
use App\Services\RoleService;
use Illuminate\Support\Facades\Hash;
use Session;
class RolesController extends Controller
{
    public function index(RolesDataTable $dataTable)
    {
        return $dataTable->render('roles.index');
    }


    public function create()
    {
        return view('roles.create');
    }


    public function save(RoleRequest $request)
    {
        RoleService::saveRole($request);
        Session::flash('success','Success|Record Saved Successfully!');
        return back();
    }


    public function show($id)
    {
        dd('Show');
    }


    public function edit($id)
    {
        $roleModel = Role::find($id);
        if($roleModel == null){
            Session::flash('error','Error|No record found!');
            return back();
        }
        return view('roles.edit',['model'=>$roleModel]);
    }


    public function update(RoleRequest $request, $id)
    {
        RoleService::updateRole($request,$id);
        Session::flash('success','Success|Record Updated Successfully!');
        return redirect()->route('roles');
    }


    public function delete($id)
    {
        $deleteRole = RoleService::deleteRole($id);
        if($deleteRole == false){
            Session::flash('error','Error|Remove user associated with role before!');
            return back();
        }else{
            Session::flash('success','Success|Record deleted successfully!');
            return back();
        }
    }
}
