<?php

namespace App\Http\Controllers;

use App\CookingReport;
use App\DataTables\CookingReportDataTable;
use App\Http\Requests\CookingReportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Session;

class CookingController extends Controller
{
    public function index(CookingReportDataTable $dataTable){
        return $dataTable->render('cooking.index');
    }

    public function create(){
        return view('cooking.create');
    }

    public function save(CookingReportRequest $request){
        $cookingReportModel = new CookingReport;
        $cookingReportModel->fill($request->except(['image']));
        if($request->hasFile('image')){
            $fileExtension = $request->file('image')->getClientOriginalExtension();
            $randomName = Str::random(10);
            $request->file('image')->move('cooking_images',$randomName.'.'.$fileExtension);
            $cookingReportModel->image = $randomName.'.'.$fileExtension;
        }
        $cookingReportModel->save();
        Session::flash('success','Success|Remarks saved successfully!');
        return back();
    }

    public function edit($id){
        $cookingReportModel = CookingReport::find($id);
        return view('cooking.edit',['model'=>$cookingReportModel]);
    }

    public function update(CookingReportRequest $request,$id){
        $cookingReportModel = CookingReport::find($id);
        $cookingReportModel->fill($request->except(['image']));
        if($request->hasFile('image')){
            $fileExtension = $request->file('photo')->getClientOriginalExtension();
            $randomName = Str::random(10);
            $request->file('photo')->move('cooking_images',$randomName.'.'.$fileExtension);
            $cookingReportModel->image = $randomName.'.'.$fileExtension;
        }
        $cookingReportModel->save();
        Session::flash('success','Success|Remarks saved successfully!');
        return redirect()->route('cooking-reports');
    }

    public function delete($id){
        $cookingReportModel = CookingReport::find($id);
        $cookingReportModel->delete();
        Session::flash('success','Success|Record deleted successfully!');
        return back();
    }
}
