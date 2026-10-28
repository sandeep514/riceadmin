<?php

namespace App\Http\Controllers;

use App\PaddyStateModel;
use App\PaddyMandiModel;
use App\PaddyPrice;
use App\PaddyPriceDefault;
use App\PaddyQuality;
use App\PaddyTradeCurrentStatus;
use App\Export\PaddyPriceExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

        $marketStatus = PaddyTradeCurrentStatus::current();
        $marketStatusLabels = PaddyTradeCurrentStatus::$marketStatus;
        $currentMarketStatus = (int) $marketStatus->currentStatus;
        $currentMarketLabel = $marketStatusLabels[$currentMarketStatus] ?? $marketStatus->message;

        $priceDefaults = PaddyPriceDefault::current();

        return view('paddyPrices.index', compact(
            'paddyPrices',
            'paddyStateModel',
            'paddyMandiModel',
            'quality',
            'from',
            'to',
            'marketStatus',
            'marketStatusLabels',
            'currentMarketStatus',
            'currentMarketLabel',
            'priceDefaults'
        ));
    }

    /**
     * Save admin-chosen default "View by" (Mandis / Crops) for paddy prices.
     * Frontend reads it from the prices API to keep the same button active.
     */
    public function saveDefaults(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'default_view_by' => 'required|in:mandi,crop',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $row = PaddyPriceDefault::current();
        $row->update([
            'default_view_by' => $request->input('default_view_by'),
        ]);

        Session::flash('success', 'Success|Default view saved successfully.');

        return redirect()->route('list.paddy.price');
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
            $query->where('created_at', '>=', Carbon::parse($from, config('app.timezone', 'Asia/Kolkata'))->startOfDay());
        }
        if ($to) {
            $query->where('created_at', '<=', Carbon::parse($to, config('app.timezone', 'Asia/Kolkata'))->endOfDay());
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
            'minHandCutting' => 'nullable|string|max:256',
            'handCutting' => 'nullable|string|max:256',
            'minMachineCutting' => 'nullable|string|max:256',
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

        // Every submission is historical data: always INSERT a brand-new row.
        // Never UPDATE a previous row here: a new date must create a new id
        // and leave existing rows (and their updated_at) completely untouched.
        // NOTE: created_at is the admin-entered price date (start of day), while
        // updated_at is the actual save time — so a back-dated insert naturally
        // looks like created_at=2026-10-12 / updated_at=2026-10-14. That is a NEW
        // row, not an update of the 2026-10-12 row. Check the row id to confirm.
        // hand_cutting_price / machine_cutting_price store MAX values.
        $paddyPrice = new PaddyPrice([
            'mandi' => (int) $request->mandi,
            'state' => (int) $mandiData->state_id,
            'quality_id' => (int) $request->quality_id,
            'crop_year' => (int) $request->crop_year,
            'min_hand_cutting_price' => $request->minHandCutting ?? '----',
            'hand_cutting_price' => $request->handCutting ?? '----',
            'min_machine_cutting_price' => $request->minMachineCutting ?? '----',
            'machine_cutting_price' => $request->machineCutting ?? '----',
            'moisture' => $request->moisture ?? '----',
            'total_arrivals' => $request->bags ?? '----',
            'change' => $request->change ?? '----',
            'status' => 1,
        ]);
        $paddyPrice->created_at = $entryDate;
        $paddyPrice->updated_at = now();
        $paddyPrice->save();
        $paddyPrice->refresh();

        // Sibling rows for the same price date + scope are intentionally left
        // untouched — the new submission is always its own row (new id).
        $siblingIds = PaddyPrice::query()
            ->where('id', '!=', $paddyPrice->id)
            ->whereDate('created_at', $entryDate->format('Y-m-d'))
            ->where('mandi', $paddyPrice->mandi)
            ->where('state', $paddyPrice->state)
            ->where('quality_id', $paddyPrice->quality_id)
            ->where('crop_year', $paddyPrice->crop_year)
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $message = 'Paddy Price created successfully as new row #' . $paddyPrice->id . '.';
        if ($siblingIds !== []) {
            $message .= ' Existing row(s) #' . implode(', #', $siblingIds) . ' for the same date left untouched.';
        }

        return redirect()->route('list.paddy.price')->with('success', $message);
    }

    public function show($id)
    {
        $paddyPrice = PaddyPrice::findOrFail($id);

        return view('paddyPrices.show', compact('paddyPrice'));
    }

    public function edit(Request $request, $id)
    {
        $paddyPrice = PaddyPrice::findOrFail($id);
        [$from, $to] = $this->resolvedFilterDates($request);

        $paddyStateModel = PaddyStateModel::where('status', 1)
            ->orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('id')
            ->get();
        $paddyMandiModel = PaddyMandiModel::where('status', 1)
            ->orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('id')
            ->get();
        $quality = $this->activePaddyQualities();

        return view('paddyPrices.edit', compact(
            'paddyPrice',
            'paddyStateModel',
            'paddyMandiModel',
            'quality',
            'from',
            'to'
        ));
    }

    public function update(Request $request, $id)
    {
        $paddyPrice = PaddyPrice::findOrFail($id);

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
            'min_hand_cutting_price' => 'nullable|string|max:256',
            'hand_cutting_price' => 'nullable|string|max:256',
            'min_machine_cutting_price' => 'nullable|string|max:256',
            'machine_cutting_price' => 'nullable|string|max:256',
            'moisture' => 'nullable|string|max:256',
            'total_arrivals' => 'nullable|string|max:256',
            'change' => 'nullable|string|max:256',
            'status' => 'required|in:0,1',
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

        $paddyPrice->mandi = (int) $request->mandi;
        $paddyPrice->state = (int) $mandiData->state_id;
        $paddyPrice->quality_id = (int) $request->quality_id;
        $paddyPrice->crop_year = (int) $request->crop_year;
        $paddyPrice->min_hand_cutting_price = $request->min_hand_cutting_price ?? '----';
        $paddyPrice->hand_cutting_price = $request->hand_cutting_price ?? '----';
        $paddyPrice->min_machine_cutting_price = $request->min_machine_cutting_price ?? '----';
        $paddyPrice->machine_cutting_price = $request->machine_cutting_price ?? '----';
        $paddyPrice->moisture = $request->moisture ?? '----';
        $paddyPrice->total_arrivals = $request->total_arrivals ?? '----';
        $paddyPrice->change = $request->change ?? '----';
        $paddyPrice->status = (int) $request->status;
        $paddyPrice->created_at = $entryDate;
        $paddyPrice->updated_at = now();
        $paddyPrice->save();

        Session::flash('success', 'Success|Paddy price updated successfully.');

        return redirect()->route('list.paddy.price', array_filter([
            'from' => $this->validFilterDate($request->input('from')),
            'to' => $this->validFilterDate($request->input('to')),
        ]));
    }

    public function destroy(Request $request, $id)
    {
        try {
            $request->validate([
                'pin' => ['required', 'string', 'in:22334455'],
            ], [
                'pin.in' => 'Invalid security PIN.',
            ]);
        } catch (ValidationException $e) {
            Session::flash('error', 'Error|' . ($e->validator->errors()->first('pin') ?: 'Invalid security PIN.'));
            return back();
        }

        $paddyPrice = PaddyPrice::find($id);
        if (! $paddyPrice) {
            Session::flash('error', 'Error|Paddy price not found.');
            return back();
        }

        $paddyPrice->delete();
        Session::flash('success', 'Success|Paddy price deleted successfully.');

        return redirect()->route('list.paddy.price', array_filter([
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ]));
    }
}