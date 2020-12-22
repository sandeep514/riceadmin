<?php

namespace App\Http\Controllers;

use App\DataTables\DealsDatatable;
use App\Deal;
use App\Http\Requests\DealFormRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Session;

class DealsController extends Controller
{
    public function index(DealsDatatable $datatable){
        return $datatable->render('deals.index');
    }

    public function create(){
        return view('deals.create');
    }

    public function save(DealFormRequest $request){
        $dealModel = new Deal;
        $dealModel->fill($request->all());
        $dealModel->date = Carbon::parse($request->date)->format('Y-m-d');
        $dealModel->user_id = Auth::user()->id;
        if($request->has('document')){
            $fileExtension = $request->file('document')->getClientOriginalExtension();
            $randomName = Str::random(10);
            $request->file('document')->move('contract-files',$randomName.'.'.$fileExtension);
            $dealModel->document = $randomName.'.'.$fileExtension;
        }
        $dealModel->save();
        Session::flash('success','Success|Record saved successfully!');
        return back();
    }

    public function edit($id){
        $dealModel = Deal::find($id);
        return view('deals.edit',['model'=>$dealModel]);
    }

    public function update(DealFormRequest $request, $id){
        $dealModel = Deal::find($id);
        $dealModel->fill($request->all());
        $dealModel->date = Carbon::parse($request->date)->format('Y-m-d');
        $dealModel->user_id = Auth::user()->id;
        if($request->has('document')){
            $fileExtension = $request->file('document')->getClientOriginalExtension();
            $randomName = Str::random(10);
            $request->file('document')->move('contract-files',$randomName.'.'.$fileExtension);
            $dealModel->document = $randomName.'.'.$fileExtension;
        }
        $dealModel->save();
        Session::flash('success','Success|Record saved successfully!');
        return redirect()->route('deals');
    }

    public function delete($id){
        $dealModel = Deal::find($id);
        $dealModel->delete();
        Session::flash('success','Success|Record deleted successfully!');
        return back();
    }
}
