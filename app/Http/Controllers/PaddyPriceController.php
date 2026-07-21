<?php

namespace App\Http\Controllers;

use App\PaddyStateModel;
use App\PaddyMandiModel;
use App\PaddyPrice;
use App\RiceName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PaddyPriceController extends Controller
{
    public function index()
    {
        $paddyPrices = PaddyPrice::with(['getMandi_rel','getState_rel','quality_rel'])->orderBy('id' , 'DESC')->get();
        
        $paddyStateModel = PaddyStateModel::where('status', 1)
            ->orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('id')
            ->get();
        $paddyMandiModel = PaddyMandiModel::where('status', 1)
            ->orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('id')
            ->get();
        $quality = RiceName::where('status' , 1)->get();

        return view('paddyPrices.index', compact('paddyPrices' , 'paddyStateModel' , 'paddyMandiModel','quality'));
    }

    public function create()
    {
        $paddyStateModel = PaddyStateModel::where('status', 1)
            ->orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('id')
            ->get();
        $paddyMandiModel = PaddyMandiModel::where('status', 1)
            ->orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('id')
            ->get();

        return view('paddyPrices.create' , compact('paddyStateModel' , 'paddyMandiModel'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quality_id' => 'required|integer',
            'mandi' => 'required',
            // 'state' => 'required|string|max:256',
            // 'handCutting' => 'required|string|max:256',
            // 'machineCutting' => 'required|string|max:256',
            // 'moisture' => 'required|string|max:256',
            // 'bags' => 'required|string|max:256',
            // 'change' => 'required|string|max:256'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $mandiData = PaddyMandiModel::where('id' , $request->mandi)->first();
        $todayDate = Carbon::today()->format('Y-m-d');

        // $lastEnterRow = PaddyPrice::orderBy('created_at' , 'desc')->first();
        // if( $lastEnterRow ){
        //     $lastEnterDate = $lastEnterRow->created_at->format('Y-m-d');

            
        //     if( $lastEnterDate != $todayDate ){
        //         $lastEnteredRecords = PaddyPrice::select("mandi","state","quality_id","hand_cutting_price","machine_cutting_price","moisture","total_arrivals","change","status")->whereDate('created_at' , $lastEnterDate)->get()
        //             ->map(function ($item) {
        //                 $item['created_at'] = now();
        //                 return $item;
        //             })
        //             ->toArray();
        //         PaddyPrice::insert($lastEnteredRecords);
        //     }
        // }
        $paddy = PaddyPrice::where('mandi', $request->mandi)
            ->where('state', $mandiData->state_id)
            ->where('quality_id', $request->quality_id)
            ->whereDate('created_at', $todayDate);
            

        if ($paddy->first()) {
            // update existing
            $paddy->update([
                'hand_cutting_price'   => $request->handCutting ?? '----',
                'machine_cutting_price'=> $request->machineCutting ?? '----',
                'moisture'             => $request->moisture ?? '----',
                'total_arrivals'       => $request->bags ?? '----',
                'change'               => $request->change ?? '----',
                'status'               => 1
            ]);
        } else {
            // create new
            PaddyPrice::create([
                'mandi'                => $request->mandi,
                'state'                => $mandiData->state_id,
                'quality_id'           => $request->quality_id,
                'hand_cutting_price'   => $request->handCutting ?? '----',
                'machine_cutting_price'=> $request->machineCutting ?? '----',
                'moisture'             => $request->moisture ?? '----',
                'total_arrivals'       => $request->bags ?? '----',
                'change'               => $request->change ?? '----',
                'status'               => 1
            ]);
        }

       
        
        
        // PaddyPrice::create($data);
        return redirect()->route('list.paddy.price')->with('sucess', 'Paddy Price created successfully.');
    }

    public function show(PaddyPrice $paddyPrice)
    {
        return view('paddyPrices.show', compact('paddyPrice'));
    }

    public function edit(PaddyPrice $paddyPrice)
    {
        return view('paddyPrices.edit', compact('paddyPrice'));
    }

    public function update(Request $request, PaddyPrice $paddyPrice)
    {
        $validator = Validator::make($request->all(), [
            'quality_id' => 'required|integer',
            'hand_cutting_price' => 'required|string|max:256',
            'machine_cutting_price' => 'required|string|max:256',
            'moisture' => 'required|string|max:256',
            'total_arrivals' => 'required|string|max:256',
            'change' => 'required|string|max:256',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $paddyPrice->update($request->all());
        return redirect()->route('paddy-prices.index')->with('success', 'Paddy Price updated successfully.');
    }

    public function destroy(PaddyPrice $paddyPrice)
    {
        $paddyPrice->delete();
        return redirect()->route('paddy-prices.index')->with('success', 'Paddy Price deleted successfully.');
    }
}