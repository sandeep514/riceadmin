<?php

namespace App\Http\Controllers;


use App\DataTables\SampleOutwardsDataTable;
use App\Http\Requests\SampleOutwardRequest;
use App\SampleOutward;
use App\Services\SampleOutwardService;
use Session;

class SampleOutwardsController extends Controller
{
    public function index(SampleOutwardsDataTable $dataTable)
    {
        return $dataTable->render('sample-outwards.index');
    }

    public function create()
    {
        return view('sample-outwards.create');
    }


    public function save(SampleOutwardRequest $request)
    {
        SampleOutwardService::saveSampleOutward($request);
        Session::flash('success','Success|Record Saved Successfully!');
        return back();
    }


    public function show($id)
    {
        dd('Show');
    }


    public function edit($id)
    {
        $sampleOutwardModel = SampleOutward::find($id);
        if($sampleOutwardModel == null){
            Session::flash('error','Error|No record found!');
            return back();
        }
        return view('sample-outwards.edit',['model'=>$sampleOutwardModel]);
    }


    public function update(SampleOutwardRequest $request, $id)
    {
        SampleOutwardService::updateSampleOutward($request,$id);
        Session::flash('success','Success|Record Updated Successfully!');
        return redirect()->route('sample-outwards');
    }


    public function delete($id)
    {
        $deleteSampleOutward = SampleOutwardService::deleteSampleOutward($id);
        if($deleteSampleOutward == false){
            Session::flash('error','Error|Remove user associated with role before!');
            return back();
        }else{
            Session::flash('success','Success|Record deleted successfully!');
            return back();
        }
    }
}
