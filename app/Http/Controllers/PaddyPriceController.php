<?php

namespace App\Http\Controllers;

use App\PaddyStateModel;
use App\PaddyMandiModel;
use App\PaddyPrice;
use App\PaddyQuality;
use App\Export\PaddyPriceExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class PaddyPriceController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->resolvedFilterDates($request);

        $export = strtolower((string) $request->input('export', ''));
        if ($export === 'excel') {
            $paddyPrices = $this->filteredPaddyPrices($from, $to, paginate: false);
            $filename = 'paddy-prices_'.($from ?? 'start').'_'.($to ?? 'end').'.xlsx';

            return Excel::download(new PaddyPriceExport($paddyPrices), $filename);
        }
        if ($export === 'pdf') {
            $paddyPrices = $this->filteredPaddyPrices($from, $to, paginate: false);
            $filename = 'paddy-prices_'.($from ?? 'start').'_'.($to ?? 'end').'.pdf';
            $pdf = Pdf::loadView('paddyPrices.pdf', compact('paddyPrices', 'from', 'to'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filename);
        }

        $paddyPrices = $this->filteredPaddyPrices($from, $to, paginate: true);

        $paddyStateModel = PaddyStateModel::where('status', 1)
            ->orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('id')
            ->get();
        $paddyMandiModel = PaddyMandiModel::where('status', 1)
            ->orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('id')
            ->get();
        $quality = $this->activePaddyQualities();

        return view('paddyPrices.index', compact(
            'paddyPrices',
            'paddyStateModel',
            'paddyMandiModel',
            'quality',
            'from',
            'to'
        ));
    }

    private function activePaddyQualities()
    {
        return PaddyQuality::query()
            ->where('status', 1)
            ->orderByRaw('`order` IS NULL, `order` ASC')
            ->orderBy('id')
            ->get();
    }

    private function resolvedFilterDates(Request $request): array
    {
        $from = $this->validFilterDate($request->input('from'));
        $to = $this->validFilterDate($request->input('to'));
        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    private function filteredPaddyPrices(?string $from, ?string $to, bool $paginate = false)
    {
        $query = PaddyPrice::with(['getMandi_rel', 'getState_rel', 'quality_rel'])
            ->orderBy('id', 'DESC');

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($paginate) {
            return $query->paginate(25)->appends(request()->except(['page', 'export']));
        }

        return $query->get();
    }

    private function validFilterDate($value): ?string
    {
        $value = is_string($value) ? trim($value) : '';
        if ($value === '' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
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
            'quality_id' => 'required|integer|exists:paddy_qualities,id',
            'state' => 'required|integer|exists:paddyStates,id',
            'mandi' => [
                'required',
                'integer',
                Rule::exists('paddyMandi', 'id')->where(function ($query) use ($request) {
                    $query->where('state_id', $request->state)->where('status', 1);
                }),
            ],
            'crop_year' => 'required|integer|digits:4|min:1900|max:'.now()->year,
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
            'crop_year' => (int) $request->crop_year,
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
            'quality_id' => 'required|integer|exists:paddy_qualities,id',
            'crop_year' => 'required|integer|digits:4|min:1900|max:'.now()->year,
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