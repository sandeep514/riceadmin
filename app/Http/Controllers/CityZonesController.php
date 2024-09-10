<?php

namespace App\Http\Controllers;

use App\CityZone;
use App\DataTables\CityZonesDataTable;
use App\Http\Requests\ZonesRequest;
use App\Services\ZonesService;
use Illuminate\Http\Request;
use Session;
class CityZonesController extends Controller
{
    public function index(CityZonesDataTable $dataTable)
    {
        return $dataTable->render('cityzones.index');
    }


    public function create()
    {
        return view('cityzones.create');
    }


    public function save(ZonesRequest $request)
    {
        ZonesService::saveZone($request);
        Session::flash('success','Success|Record Saved Successfully!');
        return back();
    }


    public function show($id)
    {
        dd('Show');
    }


    public function edit($id)
    {
        $zoneModel = CityZone::find($id);
        if($zoneModel == null){
            Session::flash('error','Error|No record found!');
            return back();
        }
        return view('cityzones.edit',['model'=>$zoneModel]);
    }


    public function update(ZonesRequest $request, $id)
    {
        ZonesService::updateZone($request,$id);
        Session::flash('success','Success|Record Updated Successfully!');
        return redirect()->route('zones');
    }


    public function delete($id)
    {
        $zoneModel = ZonesService::deleteZone($id);
        if($zoneModel == false){
            Session::flash('error','Error|Remove user associated with role before!');
            return back();
        }else{
            Session::flash('success','Success|Record deleted successfully!');
            return back();
        }
    }
}
