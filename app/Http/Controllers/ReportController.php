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
        $export = $request->get('export');

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

        // Full export (CSV) with all matching rows, ignoring pagination
        if (!empty($export) && $export === 'csv') {
            $allRows = $query->orderBy('created_at', 'desc')->get();
            $filename = 'live_prices_' . ($from ?? 'start') . '_' . ($to ?? 'end') . ($cropYear ? '_'.$cropYear : '') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];
            $callback = function() use ($allRows) {
                $out = fopen('php://output', 'w');
                // CSV header
                fputcsv($out, ['Rice Name','Rice Form','Date','Crop Year','Min Price','Max Price','Opening','Closing']);
                foreach ($allRows as $r) {
                    fputcsv($out, [
                        optional($r->name_rel)->name,
                        optional($r->form_rel)->form_name,
                        Carbon::parse($r->created_at)->format('Y-m-d'),
                        $r->cropYear,
                        $r->min_price,
                        $r->max_price,
                        $r->opening,
                        $r->closing
                    ]);
                }
                fclose($out);
            };
            return response()->stream($callback, 200, $headers);
        }

        // Paginated table for screen
        $rows = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('reports.live_prices', [
            'rows' => $rows,
            'from' => $from,
            'to'   => $to,
            'cropYears' => $cropYears,
            'cropYear' => $cropYear
        ]);
    }
}
