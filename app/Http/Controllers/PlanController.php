<?php

namespace App\Http\Controllers;

use App\Courier;
use App\LivePrice;
use App\MillStatus;
use App\Packing;
use App\PackingType;
use App\Quality;
use App\Repositories\CourierRepository;
use App\Sample;
use App\User;
use App\Port;
use App\Gallery;
use App\RiceName;
use App\RiceType;
use App\RiceForm;
use App\SubPlan;
use App\Plan;
use App\ChartInterval;
use App\DataTables\PlanDataTable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Session;

class PlanController extends Controller
{
    public function index(PlanDataTable $dataTable){
        
        return $dataTable->render('plans.index');
        
        $plan = Plan::get();
        $sub_plan = [];
        $chart_int=[];
        $data = [];
        
        foreach( $plan as $k => $v ){
            $data[$v->plan_name]['plan'] = $v;

            $sub_plan[] = json_decode($v->sub_plan , true);
            $chart_int = json_decode($v->chart_int , true);
            
            $SubPlan = [];
            foreach( $sub_plan as $key => $value ){
                foreach($value as $ke => $val){
                    $SubPlan[$ke]['data'] = SubPlan::where(['id' => $ke])->first();
                    $SubPlan[$ke]['price'] = $value;
                }
            }
    
            $data[$v->plan_name]['SubPlan'] = $SubPlan;

            $data[$v->plan_name]['ChartInt'] = ChartInterval::select('id', 'name')->whereIn('id' , array_values($chart_int))->get();
        }
        return view('plans.index', compact('data'));
    }
    public function create(){
        $SubPlan = SubPlan::get();
        $ChartInterval = ChartInterval::get();
        
        return view('plans.create', compact('SubPlan','ChartInterval'));
    }
   
   public function save(Request $request){
        $name = $request->plan_name;
        $chartint = array_filter($request->chartint);
        $subplan = array_filter($request->subplan);
       
        $plan = Plan::create([
                    'plan_name' => $name,
                    'sub_plan' => json_encode($subplan),
                    'chart_int' => json_encode($chartint),
                    'price' => 0
                ]);
       
       return back();
   }
   
   public function editPlan($id){
        $plan = Plan::whereId($id)->get();
        $ChartInterval = ChartInterval::get();
        
        $SubPlans = SubPlan::get();
        $sub_plan = [];
        $chart_int=[];
        $data = [];
        
        foreach( $plan as $k => $v ){
            $data[$v->plan_name]['plan'] = $v;

            $sub_plan[] = json_decode($v->sub_plan , true);
            $chart_int = json_decode($v->chart_int , true);

            $SubPlan = [];
            foreach( $sub_plan as $key => $value ){
                foreach($value as $ke => $val){
                    $SubPlan[$ke]['data'] = SubPlan::where(['id' => $ke])->first();
                    $SubPlan[$ke]['price'] = $val;
                }
            }

            $data[$v->plan_name]['SubPlan'] = $SubPlan;
            $data[$v->plan_name]['ChartInt'] = ChartInterval::select('id', 'name')->whereIn('id' , array_values($chart_int))->get();
        }
        $chartInt = ChartInterval::select('id')->whereIn('id' , array_values($chart_int))->get()->pluck('id');
        $chartIntArray = $chartInt->toArray();
        
        return view('plans.edit', compact('data' ,'SubPlans' , 'SubPlan', 'ChartInterval', 'plan' , 'chartInt','chartIntArray'));
   }
   
    public function updatePlan(Request $request){

        $id = $request->plan_id;
        $name = $request->plan_name;
        $chartint = array_filter($request->chartint);
        $subplan = array_filter($request->subplan);

        $plan = Plan::where('id' , $id)->update([
                                                'plan_name' => $name,
                                                'sub_plan' => json_encode($subplan),
                                                'chart_int' => json_encode($chartint),
                                                'price' => 0
                                            ]);
       
       return back();
    }
   
    public function deletePlan($id){
        $plan = Plan::whereId($id)->delete();
        Session::flash('success' , 'Plan deleted successfully.');
        return back();
    }
}