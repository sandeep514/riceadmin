<?php

namespace App\Http\Controllers;

use App\DataTables\SamplesDataTable;
use App\Http\Requests\SampleRequest;
use App\Sample;
use App\Services\SampleService;
use Illuminate\Http\Request;
use Session;
class SamplesController extends Controller
{

    public function index(SamplesDataTable $dataTable)
    {
        return $dataTable->render('samples.index');
    }

    public function create()
    {
        return view('samples.create');
    }


    public function save(SampleRequest $request)
    {
        SampleService::saveSample($request);
        Session::flash('success','Success|Record Saved Successfully!');
        return back();
    }


    public function show($id)
    {
        dd('Show');
    }


    public function edit($id)
    {
        $sampleModel = Sample::find($id);
        if($sampleModel == null){
            Session::flash('error','Error|No record found!');
            return back();
        }
        return view('samples.edit',['model'=>$sampleModel]);
    }


    public function update(SampleRequest $request, $id)
    {
        SampleService::updateSample($request,$id);
        Session::flash('success','Success|Record Updated Successfully!');
        return redirect()->route('samples');
    }


    public function delete($id)
    {
        $deleteSample = SampleService::deleteSample($id);
        if($deleteSample == false){
            Session::flash('error','Error|Remove user associated with role before!');
            return back();
        }else{
            Session::flash('success','Success|Record deleted successfully!');
            return back();
        }
    }
}
