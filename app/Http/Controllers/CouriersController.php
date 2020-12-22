<?php

namespace App\Http\Controllers;


use App\Courier;
use App\DataTables\CouriersDataTable;
use App\Http\Requests\CourierRequest;
use App\Services\CourierService;
use Session;
class CouriersController extends Controller
{
    public function index(CouriersDataTable $dataTable)
    {
        return $dataTable->render('couriers.index');
    }


    public function create()
    {
        return view('couriers.create');
    }


    public function save(CourierRequest $request)
    {
        CourierService::saveCourier($request);
        Session::flash('success','Success|Record Saved Successfully!');
        return back();
    }


    public function show($id)
    {
        dd('Show');
    }


    public function edit($id)
    {
        $courierModel = Courier::find($id);
        if($courierModel == null){
            Session::flash('error','Error|No record found!');
            return back();
        }
        return view('couriers.edit',['model'=>$courierModel]);
    }


    public function update(CourierRequest $request, $id)
    {
        CourierService::updateCourier($request,$id);
        Session::flash('success','Success|Record Updated Successfully!');
        return redirect()->route('couriers');
    }


    public function delete($id)
    {
        $courierModel = CourierService::deleteCourier($id);
        if($courierModel == false){
            Session::flash('error','Error|Remove user associated with role before!');
            return back();
        }else{
            Session::flash('success','Success|Record deleted successfully!');
            return back();
        }
    }
}
