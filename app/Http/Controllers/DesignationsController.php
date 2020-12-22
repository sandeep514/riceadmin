<?php

namespace App\Http\Controllers;

use App\DataTables\DesignationsDatatable;
use App\Designation;
use App\Http\Requests\DesignationRequest;
use Illuminate\Http\Request;
use Session;

class DesignationsController extends Controller
{
    public function index(DesignationsDatatable $datatable){
        return $datatable->render('designations.index');
    }

    public function create(){
        return view('designations.create');
    }

    public function save(DesignationRequest $request){
        $designationModel = new Designation;
        $designationModel->fill($request->all());
        $designationModel->save();
        Session::flash('success','Success|Record saved successfully!');
        return back();
    }

    public function edit($id){
        $designationModel = Designation::find($id);
        return view('designations.edit',['model'=>$designationModel]);
    }

    public function update(DesignationRequest $request, $id){
        $designationModel = Designation::find($id);
        $designationModel->fill($request->all());
        $designationModel->save();
        Session::flash('success','Success|Record saved successfully!');
        return redirect()->route('designations');
    }

    public function delete($id){
        $designationModel = Designation::find($id);
        $designationModel->delete();
        Session::flash('success','Success|Record deleted successfully!');
        return back();
    }
}
