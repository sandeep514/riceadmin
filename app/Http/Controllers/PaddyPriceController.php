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
            'date' => 'required|date_format:Y-m-d|before_or_equal:today',
            'quality_id' => 'required|integer|exists:rice_names,id',
            'mandi' => 'required|integer|exists:paddyMandi,id',
            'handCutting' => 'nullable|string|max:256',
            'machineCutting' => 'nullable|string|max:256',
            'moisture' => 'nullable|string|max:256',
            'bags' => 'nullable|string|max:256',
            'change' => 'nullable|string|max:256',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $mandiData = PaddyMandiModel::findOrFail($request->mandi);
        $entryDate = Carbon::createFromFormat(
            'Y-m-d',
            $request->date,
            config('app.timezone', 'Asia/Kolkata')
        )->startOfDay();

        // Every submission is historical data: always insert a new row, even if
        // another row has the same date, mandi, state, quality and price values.
        $paddyPrice = new PaddyPrice([
            'mandi' => (int) $request->mandi,
            'state' => (int) $mandiData->state_id,
            'quality_id' => (int) $request->quality_id,
            'hand_cutting_price' => $request->handCutting ?? '----',
            'machine_cutting_price' => $request->machineCutting ?? '----',
            'moisture' => $request->moisture ?? '----',
            'total_arrivals' => $request->bags ?? '----',
            'change' => $request->change ?? '----',
            'status' => 1,
        ]);
        $paddyPrice->created_at = $entryDate;
        $paddyPrice->updated_at = now();
        $paddyPrice->save();

        return redirect()->route('list.paddy.price')->with('success', 'Paddy Price created successfully.');
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