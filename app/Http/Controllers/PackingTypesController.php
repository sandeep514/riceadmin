<?php

namespace App\Http\Controllers;

use App\DataTables\PackingTypesDataTable;
use App\Http\Requests\PackingTypeRequest;
use App\PackingType;
use App\Services\PackingTypeService;
use Illuminate\Http\Request;
use Session;

class PackingTypesController extends Controller
{
    public function index(PackingTypesDataTable $dataTable)
    {
        return $dataTable->render('packing-types.index');
    }


    public function create()
    {
        return view('packing-types.create');
    }


    public function save(PackingTypeRequest $request)
    {
        PackingTypeService::savePackingType($request);
        Session::flash('success','Success|Record Saved Successfully!');
        return back();
    }


    public function show($id)
    {
        dd('Show');
    }


    public function edit($id)
    {
        $packingTypeModel = PackingType::find($id);
        if($packingTypeModel == null){
            Session::flash('error','Error|No record found!');
            return back();
        }
        return view('packing-types.edit',['model'=>$packingTypeModel]);
    }


    public function update(PackingTypeRequest $request, $id)
    {
        PackingTypeService::updatePackingType($request,$id);
        Session::flash('success','Success|Record Updated Successfully!');
        return redirect()->route('packing-types');
    }


    public function delete($id)
    {
        $deletePackingType = PackingTypeService::deletePackingType($id);
        if($deletePackingType == false){
            Session::flash('error','Error|Remove user associated with role before!');
            return back();
        }else{
            Session::flash('success','Success|Record deleted successfully!');
            return back();
        }
    }
}
