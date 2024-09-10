<?php

namespace App\Http\Controllers;


use App\DataTables\SampleRegistersDataTable;
use App\Http\Requests\SampleRegisterRequest;
use App\Sample;
use App\SampleRegister;
use App\Services\SampleRegisterService;
use Session;

class SampleRegistersController extends Controller
{
    public function index(SampleRegistersDataTable $dataTable)
    {
        return $dataTable->render('sample-registers.index');
    }

    public function create($sample = null)
    {
        $sampleModel = null;
        if($sample != null && $sample != 'new'){
            $sampleModel = Sample::find($sample);
        }
        $maxSntc = SampleRegisterService::getMaxSntc();
        return view('sample-registers.create',['sampleModel'=>$sampleModel,'maxSntc'=>$maxSntc]);
    }


    public function save(SampleRegisterRequest $request)
    {
        SampleRegisterService::saveSampleRegister($request);
        Session::flash('success','Success|Record Saved Successfully!');
        return back();
    }


    public function show($id)
    {
        dd('Show');
    }


    public function edit($id)
    {
        $sampleRegisterModel = SampleRegister::find($id);
        if($sampleRegisterModel == null){
            Session::flash('error','Error|No record found!');
            return back();
        }
        $sampleRegisterModel->sntc_no = str_pad($sampleRegisterModel->sntc_no, 5,0,STR_PAD_LEFT);
        return view('sample-registers.edit',['model'=>$sampleRegisterModel]);
    }


    public function update(SampleRegisterRequest $request, $id)
    {
        SampleRegisterService::updateSampleRegister($request,$id);
        Session::flash('success','Success|Record Updated Successfully!');
        return redirect()->route('sample-registers');
    }


    public function delete($id)
    {
        $deleteSampleRegister = SampleRegisterService::deleteSampleRegister($id);
        if($deleteSampleRegister == false){
            Session::flash('error','Error|Remove user associated with role before!');
            return back();
        }else{
            Session::flash('success','Success|Record deleted successfully!');
            return back();
        }
    }


}
