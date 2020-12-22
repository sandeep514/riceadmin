<?php

namespace App\Http\Controllers;

use App\DataTables\PackingsDataTable;
use App\Http\Requests\PackingRequest;
use App\Packing;
use App\Services\PackingService;
use Illuminate\Http\Request;
use Session;

class PackingsController extends Controller
{
    public function index(PackingsDataTable $dataTable)
    {
        return $dataTable->render('packings.index');
    }


    public function create()
    {
        return view('packings.create');
    }


    public function save(PackingRequest $request)
    {
        PackingService::savePacking($request);
        Session::flash('success','Success|Record Saved Successfully!');
        return back();
    }


    public function show($id)
    {
        dd('Show');
    }


    public function edit($id)
    {
        $packingModel = Packing::find($id);
        if($packingModel == null){
            Session::flash('error','Error|No record found!');
            return back();
        }
        return view('packings.edit',['model'=>$packingModel]);
    }


    public function update(PackingRequest $request, $id)
    {
        PackingService::updatePacking($request,$id);
        Session::flash('success','Success|Record Updated Successfully!');
        return redirect()->route('packings');
    }


    public function delete($id)
    {
        $deletePacking = PackingService::deletePacking($id);
        if($deletePacking == false){
            Session::flash('error','Error|Remove user associated with role before!');
            return back();
        }else{
            Session::flash('success','Success|Record deleted successfully!');
            return back();
        }
    }
}
