<?php

namespace App\Http\Controllers;

use App\DataTables\DocumentsDataTable;
use App\Deal;
use App\Document;
use App\Http\Requests\DocumentsFormRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Session;

class DocumentsController extends Controller
{
    public function index(DocumentsDataTable $dataTable){

        return $dataTable->render('documents.index');
    }

    public function create(){
        return view('documents.create');
    }

    public function save(DocumentsFormRequest $request){
        if(!File::exists('documents/'.$request->contract_no)){
            File::makeDirectory('documents/'.$request->contract_no);
        }
        $documentModel = new Document;
        $documentModel->fill($request->except(['contract_copy','bill_copy','bilty_copy','kanta_parchi','due_date','date']));
        foreach($request->files as $field => $file){
            $fileExtension = $file->getClientOriginalExtension();
            $randomName = Str::random(10);
            $file->move('documents/'.$request->contract_no,$randomName.'.'.$fileExtension);
            $documentModel[$field] = $randomName.'.'.$fileExtension;
        }
        $documentModel->date = Carbon::parse($request->date)->format('Y-m-d');
        $documentModel->due_date = Carbon::parse($request->due_date)->format('Y-m-d');
        $documentModel->user_id = Auth::user()->id;
        $documentModel->save();
        Session::flash('success','Success|Documents saved successfully!');
        return back();
    }

    public function edit($id){
        $documentModel = Document::find($id);
        return view('documents.edit',['model'=>$documentModel]);
    }

    public function update(DocumentsFormRequest $request, $id){
        $files = $request->files->count();
        if(!File::exists('documents/'.$request->contract_no)){
            File::makeDirectory('documents/'.$request->contract_no);
        }
        $documentModel = Document::find($id);
        $documentModel->fill($request->except(['contract_copy','bill_copy','bilty_copy','kanta_parchi','due_date','date']));
        if($files != 0){
            foreach($request->files as $field => $file){
                $fileExtension = $file->getClientOriginalExtension();
                $randomName = Str::random(10);
                $file->move('documents/'.$request->contract_no,$randomName.'.'.$fileExtension);
                $documentModel[$field] = $randomName.'.'.$fileExtension;
            }
        }
        $documentModel->date = Carbon::parse($request->date)->format('Y-m-d');
        $documentModel->due_date = Carbon::parse($request->due_date)->format('Y-m-d');
        $documentModel->user_id = Auth::user()->id;
        $documentModel->save();
        Session::flash('success','Success|Documents saved successfully!');
        return redirect()->route('documents');
    }

    public function delete($id){
        $documentModel = Document::find($id);
        $documentModel->delete();
        Session::flash('success','Success|Documents deleted successfully!');
        return redirect()->route('documents');
    }
}
