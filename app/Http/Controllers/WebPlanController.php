<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\WebPlanModel;
use App\WebPlanKeysModel;
use App\WebPlanKeysMapModel;
use Illuminate\Support\Facades\Validator;

use Session;

class WebPlanController extends Controller
{
    public function indexKeys(){
        $webPlanKeys = WebPlanKeysModel::get();
        return View('webplanskeys.index' , compact('webPlanKeys'));
    }

    public function createKeys(){
        return View('webplanskeys.create');
    }

    public function saveKeys(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'planKey' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        WebPlanKeysModel::create([
            'key' => $request->planKey
        ]);

        Session::flash('success' , 'Plan key generated successfully|');
        return back();
    }

    public function editKeys($webPlanKeyId)
    {
        $data = WebPlanKeysModel::where('id' , $webPlanKeyId)->first();
        return view('webplanskeys.edit' , compact('data'));
    }

    public function updateKeys(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'planKey' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        WebPlanKeysModel::where('id' , $request->id)->update([
            'key' => $request->planKey
        ]);

        Session::flash('success' , 'Plan key updated successfully|');
        return back();
    }



    public function indexPlan(){
        $webPlanKeys = WebPlanModel::get();
        return View('webplans.index' , compact('webPlanKeys'));
    }

    public function createPlan(){
        $WebPlanKeysModel = WebPlanKeysModel::get();
        return View('webplans.create',compact('WebPlanKeysModel'));
    }

    public function savePlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $WebPlanModel = WebPlanModel::create([
            'title' => $request->plan
        ]);
        $planKeyMap = [];
        if( $request->available  ){
            foreach ($request->available as $key => $value) {
                $planKeyMap[] = ['plan_id' => $WebPlanModel->id, 'key_id' => $value ,'value' => 1 ];
            }
        }
        
        WebPlanKeysMapModel::insert($planKeyMap);

        Session::flash('success' , 'Plan key generated successfully|');
        return back();
    }

    public function editPlan($webPlanId)
    {
        $data = WebPlanModel::where('id' , $webPlanId)->with(['getPlanKeyMap' => function($q){
            // return $q->with(['getPlanKey']);
        }])->first();
        $selectedMapKeys = $data->getPlanKeyMap->pluck( 'value' , 'key_id' );
        $WebPlanKeysModel = WebPlanKeysModel::get();
        return view('webplans.edit' , compact('data','selectedMapKeys','WebPlanKeysModel'));
    }

    public function updatePlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $WebPlanModel = WebPlanModel::where('id' , $request->id)->update([
            'title' => $request->plan
        ]);
        $planKeyMap = [];
        if( $request->available  ){
            foreach ($request->available as $key => $value) {
                $planKeyMap[] = ['plan_id' => $request->id, 'key_id' => $value ,'value' => 1 ];
            }
            WebPlanKeysMapModel::where('plan_id' , $request->id)->delete();
            WebPlanKeysMapModel::insert($planKeyMap);
        }
        

        Session::flash('success' , 'Plan key updated successfully|');
        return back();
    }

}