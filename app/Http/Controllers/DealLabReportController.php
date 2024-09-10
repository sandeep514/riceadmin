<?php

namespace App\Http\Controllers;

use App\DataTables\DealLabReportsDataTable;
use App\DealLabReport;
use App\Http\Requests\DealLabReportRequest;
use Illuminate\Http\Request;
use Session;

class DealLabReportController extends Controller
{
    public function index(DealLabReportsDataTable $dataTable){
        return $dataTable->render('deal-lab-report.index');
    }


    public function create(){
        return view('deal-lab-report.create');
    }

    public function save(DealLabReportRequest $request){
        $dealLabReport = new DealLabReport;
        $dealLabReport->fill($request->except(['sub_ad_mixture']));
        $dealLabReport->sub_ad_mixture = json_encode($request->sub_ad_mixture);
        $dealLabReport->save();
        Session::flash('success','Success|Record saved successfully!');
        return back();
    }

    public function edit($id){
        $dealLabReportModel = DealLabReport::find($id);
        return view('deal-lab-report.edit',['model'=>$dealLabReportModel]);
    }

    public function update($id, DealLabReportRequest $request){
        $dealLabReport = DealLabReport::find($id);
        $dealLabReport->fill($request->except(['sub_ad_mixture']));
        $dealLabReport->sub_ad_mixture = json_encode($request->sub_ad_mixture);
        $dealLabReport->save();
        Session::flash('success','Success|Record saved successfully!');
        return redirect()->route('deal-lab-reports');
    }

    public function delete($id){
        $sampleLabReportModel = DealLabReport::find($id);
        $sampleLabReportModel->delete();
        Session::flash('success','Success|Record deleted successfully!');
        return back();
    }
}
