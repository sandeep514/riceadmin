<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\WebPlanModel;
use App\WebPlanKeysModel;
use App\WebPlanKeysMapModel;
use App\Events\AdminEvent;
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

    public function updateKeyStatus($id)
    {
        $webPlanKey = WebPlanKeysModel::findOrFail($id);
        $webPlanKey->update(['status' => !$webPlanKey->status]);

        broadcast(new AdminEvent('plan_key_updated', [
            'id' => $webPlanKey->id,
            'key' => $webPlanKey->key,
            'status' => (int) $webPlanKey->status,
        ]))->toOthers();

        return redirect()->route('list.web.plans.keys')->with('success', 'Key status updated successfully');
    }


    public function indexPlan(){
        $webPlanKeys = WebPlanModel::get();
        return View('webplans.index' , compact('webPlanKeys'));
    }

    public function updateStatus($id)
    {
        $webPlan = WebPlanModel::findOrFail($id);
        $webPlan->update(['status' => !$webPlan->status]);

        return redirect()->route('webplans.index')->with('success', 'Status updated successfully');
    }

    public function createPlan(){
        $WebPlanKeysModel = WebPlanKeysModel::get();
        return View('webplans.create',compact('WebPlanKeysModel'));
    }

    public function savePlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan' => 'required',
            'monthly_price' => 'required|numeric|min:0',
            'quarterly_price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'monthly_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'quarterly_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'yearly_discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $mDisc = (float) ($request->monthly_discount_percentage ?? 0);
        $qDisc = (float) ($request->quarterly_discount_percentage ?? 0);
        $yDisc = (float) ($request->yearly_discount_percentage ?? 0);
        $monthlyFinal = $request->monthly_price !== null ? round($request->monthly_price - ($request->monthly_price * $mDisc / 100), 2) : null;
        $quarterlyFinal = $request->quarterly_price !== null ? round($request->quarterly_price - ($request->quarterly_price * $qDisc / 100), 2) : null;
        $yearlyFinal = $request->yearly_price !== null ? round($request->yearly_price - ($request->yearly_price * $yDisc / 100), 2) : null;

        $WebPlanModel = WebPlanModel::create([
            'title' => $request->plan,
            'amount' => $request->monthly_price,
            'discount_percentage' => null,
            'monthly_price' => $request->monthly_price,
            'quarterly_price' => $request->quarterly_price,
            'yearly_price' => $request->yearly_price,
            'monthly_final_amount' => $monthlyFinal,
            'quarterly_final_amount' => $quarterlyFinal,
            'yearly_final_amount' => $yearlyFinal,
            'monthly_discount_percentage' => $request->monthly_discount_percentage,
            'quarterly_discount_percentage' => $request->quarterly_discount_percentage,
            'yearly_discount_percentage' => $request->yearly_discount_percentage,
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
            'planKey' => 'required',
            'monthly_price' => 'required|numeric|min:0',
            'quarterly_price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'monthly_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'quarterly_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'yearly_discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $mDisc = (float) ($request->monthly_discount_percentage ?? 0);
        $qDisc = (float) ($request->quarterly_discount_percentage ?? 0);
        $yDisc = (float) ($request->yearly_discount_percentage ?? 0);
        $monthlyFinal = $request->monthly_price !== null ? round($request->monthly_price - ($request->monthly_price * $mDisc / 100), 2) : null;
        $quarterlyFinal = $request->quarterly_price !== null ? round($request->quarterly_price - ($request->quarterly_price * $qDisc / 100), 2) : null;
        $yearlyFinal = $request->yearly_price !== null ? round($request->yearly_price - ($request->yearly_price * $yDisc / 100), 2) : null;

        $WebPlanModel = WebPlanModel::where('id' , $request->id)->update([
            'title' => $request->planKey,
            'amount' => $request->monthly_price,
            'discount_percentage' => null,
            'monthly_price' => $request->monthly_price,
            'quarterly_price' => $request->quarterly_price,
            'yearly_price' => $request->yearly_price,
            'monthly_final_amount' => $monthlyFinal,
            'quarterly_final_amount' => $quarterlyFinal,
            'yearly_final_amount' => $yearlyFinal,
            'monthly_discount_percentage' => $request->monthly_discount_percentage,
            'quarterly_discount_percentage' => $request->quarterly_discount_percentage,
            'yearly_discount_percentage' => $request->yearly_discount_percentage,
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
