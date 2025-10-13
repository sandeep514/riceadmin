<?php

namespace App\Http\Controllers;

use App\PaddyStateModel;
use App\PaddyMandiModel;
use App\DataTables\PlanDataTable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Session;
use Illuminate\Support\Facades\Validator;

class PaddyMandiController extends Controller
{
    public function listWebPaddyMandi(){
        $paddyMandi = PaddyMandiModel::orderBy('id' , 'DESC')->with(['state_rel'])->get();
        return View('paddyMandi.index' , compact('paddyMandi'));
    }

    public function createWebPaddyMandi()
    {
        $paddyState = PaddyStateModel::where('status' , 1)->get();
        return View('paddyMandi.create' , compact('paddyState'));
    }

    public function saveWebPaddyMandi(Request $request){
        $validator = Validator::make($request->all(), [
            'mandi' => 'required|string|max:256',
            'state_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        PaddyMandiModel::create(['mandi' => $request->mandi , 'state_id' => $request->state_id, 'status' => 1]);

        Session::flash('sucess' , 'Paddy Mandi created successfully.');
        return back();
    }

    public function editWebPaddyMandi($paddyMandiId){
        $paddyState = PaddyStateModel::where('status' , 1)->get();
        $data = PaddyMandiModel::where('id' , $paddyMandiId)->first();
        return View('paddyMandi.edit' , compact('data' , 'paddyState'));
    }
    
    public function updateWebPaddyMandi(Request $request){

        $validator = Validator::make($request->all(), [
            'state_id' => 'required',
            'mandi' => 'required|string|max:256',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        PaddyMandiModel::where(['id' => $request->id])->update([ 'mandi' => $request->mandi , 'state_id' => $request->state_id ]);
        Session::flash('sucess' , 'Paddy Mandi edited successfully.');
        return back();

    }


    public function updateStatus($paddyMandiId)
    {
        $paddy = PaddyMandiModel::findOrFail($paddyMandiId);
        $paddy->update([
            'status' => !$paddy->status
        ]);

        Session::flash('sucess', 'Paddy Mandi status updated successfully.');
        return back();
    }
}