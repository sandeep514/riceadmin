<?php

namespace App\Http\Controllers;

use App\DataTables\LoadingReportsDataTable;
use App\Http\Requests\LoadingReportRequest;
use App\LoadingReport;
use Illuminate\Http\Request;
use Session;

class LoadingReportController extends Controller
{
    public function index(LoadingReportsDataTable $dataTable){
        return $dataTable->render('loading.index');
    }

    public function create(){
        return view('loading.create');
    }

    public function save(LoadingReportRequest $request){
        $loadingReportModel = new LoadingReport;
        $loadingReportModel->fill($request->all());
        $loadingReportModel->save();
        Session::flash('success','Success|Record saved successfully!');
        return back();
    }

    public function edit($id){
        $loadingReportModel = LoadingReport::find($id);
        return view('loading.edit',['model'=>$loadingReportModel]);
    }

    public function update(LoadingReportRequest $request, $id){
        $loadingReportModel = LoadingReport::find($id);
        $loadingReportModel->fill($request->all());
        $loadingReportModel->save();
        Session::flash('success','Success|Record saved successfully!');
        return redirect()->route('loading-reports');
    }

    public function delete($id){
        $loadingReportModel = LoadingReport::find($id);
        $loadingReportModel->delete();
        Session::flash('success','Success|Record deleted successfully!');
        return back();
    }
}
