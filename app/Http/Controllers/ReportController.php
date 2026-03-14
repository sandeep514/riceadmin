<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LivePrice;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function livePrices(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');
        $cropYear = $request->get('crop_year');

        // Default to today's records when no dates are provided
        if (empty($from) && empty($to)) {
            $from = Carbon::today()->format('Y-m-d');
            $to   = Carbon::today()->format('Y-m-d');
        }

        // Distinct crop years for filter
        $cropYears = LivePrice::query()
            ->whereNotNull('cropYear')
            ->select('cropYear')
            ->distinct()
            ->orderBy('cropYear', 'desc')
            ->pluck('cropYear')
            ->map(fn($y) => (int) $y)
            ->toArray();

        $query = LivePrice::query()
            ->with([
                'name_rel:id,name',
                'form_rel:id,form_name'
            ])
            ->whereNotNull('name')
            ->whereNotNull('form');

        if (!empty($from)) {
            $fromDate = Carbon::parse($from)->startOfDay();
            $query->where('created_at', '>=', $fromDate);
        }
        if (!empty($to)) {
            $toDate = Carbon::parse($to)->endOfDay();
            $query->where('created_at', '<=', $toDate);
        }

        if (!empty($cropYear)) {
            $query->where('cropYear', (int) $cropYear);
        }

        $rows = $query->orderBy('created_at', 'desc')->get();

        return view('reports.live_prices', [
            'rows' => $rows,
            'from' => $from,
            'to'   => $to,
            'cropYears' => $cropYears,
            'cropYear' => $cropYear
        ]);
    }
}
