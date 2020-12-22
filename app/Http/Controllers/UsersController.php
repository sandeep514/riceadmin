<?php

namespace App\Http\Controllers;

use App\DataTables\UsersDataTable;
use App\Http\Requests\UserRequest;
use App\Services\UserService;
use App\User;
use Illuminate\Http\Request;
use Session;

class UsersController extends Controller
{

    public function index(UsersDataTable $dataTable)
    {
        return $dataTable->render('users.index');
    }


    public function create()
    {
        return view('users.create');
    }


    public function save(UserRequest $request)
    {
        UserService::saveUser($request);
        Session::flash('success','Success|Record Saved Successfully!');
        return back();
    }


    public function show($id)
    {
        dd('Show');
    }


    public function edit($id)
    {
        $userModel = User::with(['field_runner_rel','seller_rel','buyer_rel'])->find($id);
        if($userModel == null){
            Session::flash('error','Error|No record found!');
            return back();
        }
        if(request()->role == 4){
            $userModel->company = $userModel->seller_rel->company_name;
            $userModel->contact_person = $userModel->seller_rel->contact_person;
            $userModel->email_ids = json_decode($userModel->seller_rel->email_ids,true);
        }elseif(request()->role == 5){
            $userModel->company = $userModel->buyer_rel->company_name;
            $userModel->contact_person = $userModel->buyer_rel->contact_person;
            $userModel->email_ids = json_decode($userModel->buyer_rel->email_ids,true);
        }
        return view('users.edit',['model'=>$userModel]);
    }


    public function update(UserRequest $request, $id)
    {
        UserService::updateUser($request, $id);
        Session::flash('success','Success|Record Updated Successfully!');
        return redirect()->route('users',request()->role);
    }


    public function delete($id)
    {
        $sellerModel = User::find($id);
        if($sellerModel == null){
            Session::flash('error','Error|Something went wrong');
            return back();
        }
        $sellerModel->delete();
        Session::flash('success','Success|Record deleted successfully!');
        return back();
    }
}
