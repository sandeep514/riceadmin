<?php

namespace App\Http\Controllers;

use App\DataTables\QualitiesDataTable;
use App\Http\Requests\QualityRequest;
use App\Quality;
use App\Services\QualityService;
use Illuminate\Http\Request;
use Session;

class QualitiesController extends Controller
{
    public function index(QualitiesDataTable $dataTable)
    {
        return $dataTable->render('qualities.index');
    }


    public function create()
    {
        return view('qualities.create');
    }


    public function save(QualityRequest $request)
    {
        QualityService::saveQuality($request);
        Session::flash('success','Success|Record Saved Successfully!');
        return back();
    }


    public function show($id)
    {
        dd('Show');
    }


    public function edit($id)
    {
        $qualityModel = Quality::find($id);
        if($qualityModel == null){
            Session::flash('error','Error|No record found!');
            return back();
        }
        return view('qualities.edit',['model'=>$qualityModel]);
    }


    public function update(QualityRequest $request, $id)
    {
        QualityService::updateQuality($request,$id);
        Session::flash('success','Success|Record Updated Successfully!');
        return redirect()->route('roles');
    }


    public function delete($id)
    {
        $deleteQuality = QualityService::deleteQuality($id);
        if($deleteQuality == false){
            Session::flash('error','Error|Remove user associated with role before!');
            return back();
        }else{
            Session::flash('success','Success|Record deleted successfully!');
            return back();
        }
    }

    public function importQuality()
    {
        return view('qualities.import');
    }

    public function saveImportQuality(Request $request)
    {
        $importQuality = QualityService::importQuality($request);
        Session::flash('imported',$importQuality->count().' records imported successfully!');
        return back();
    }
}
