<?php

namespace App\Http\Controllers;

use App\DataTables\SampleLabReportDatatable;
use App\Http\Requests\SampleLabReportRequest;
use App\SampleLabReport;
use Illuminate\Http\Request;
use Session;

class SampleLabReportController extends Controller
{
    public function index(SampleLabReportDatatable $datatable){
        return $datatable->render('sample-lab-report.index');
    }

    public function create(){
        return view('sample-lab-report.create');
    }

    public function save(SampleLabReportRequest $request){
        $sampleLabReportModel = new SampleLabReport;
        $sampleLabReportModel->fill($request->all());
        $sampleLabReportModel->save();
        Session::flash('success','Success|Record saved successfully!');
        return back();
    }

    public function edit($id){
        $sampleLabReportModel = SampleLabReport::find($id);
        return view('sample-lab-report.edit',['model'=>$sampleLabReportModel]);
    }

    public function update($id, SampleLabReportRequest $request){
        $sampleLabReportModel = SampleLabReport::find($id);
        $sampleLabReportModel->fill($request->all());
        $sampleLabReportModel->save();
        Session::flash('success','Success|Record saved successfully!');
        return redirect()->route('sample-lab-reports');
    }

    public function delete($id){
        $sampleLabReportModel = SampleLabReport::find($id);
        $sampleLabReportModel->delete();
        Session::flash('success','Success|Record deleted successfully!');
        return back();
    }
}
