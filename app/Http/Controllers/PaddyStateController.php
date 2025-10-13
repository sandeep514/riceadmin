<?php

namespace App\Http\Controllers;

use App\PaddyStateModel;
use App\DataTables\PlanDataTable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Session;
use Illuminate\Support\Facades\Validator;

class PaddyStateController extends Controller
{
    public function listWebPaddyState(){
        $paddyState = PaddyStateModel::orderBy('id' , 'DESC')->get();
        return View('paddyState.index' , compact('paddyState'));
    }

    public function createWebPaddyState()
    {
        return View('paddyState.create');
    }

    public function saveWebPaddyState(Request $request){
        $validator = Validator::make($request->all(), [
            'state' => 'required|string|max:256',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        PaddyStateModel::create(['state' => $request->state, 'status' => 1]);

        Session::flash('sucess' , 'Paddy state created successfully.');
        return back();
    }

    public function editWebPaddyState($paddyStateId){
        $data = PaddyStateModel::where('id' , $paddyStateId)->first();
        return View('paddyState.edit' , compact('data'));
    }
    
    public function updateWebPaddyState(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'state' => 'required|string|max:256',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        PaddyStateModel::where(['id' => $request->id])->update([ 'state' => $request->state ]);
        Session::flash('sucess' , 'Paddy state edited successfully.');
        return back();

    }


    public function updateState($paddyStateId)
    {
        $paddy = PaddyStateModel::findOrFail($paddyStateId);
        $paddy->update([
            'status' => !$paddy->status
        ]);

        Session::flash('sucess', 'Paddy state status updated successfully.');
        return back();
    }
}