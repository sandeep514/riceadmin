<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LivePrice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
            ->leftJoin('rice_names', 'live_prices.name', '=', 'rice_names.id')
            ->leftJoin('rice_forms', 'live_prices.form', '=', 'rice_forms.id')
            ->leftJoin('rice_form_milestone3 as rfm3', 'live_prices.form', '=', 'rfm3.id')
            ->whereNotNull('live_prices.name')
            ->whereNotNull('live_prices.form')
            ->where('live_prices.name', '>', 0)
            ->where('live_prices.form', '>', 0)
            ->select([
                'live_prices.*',
                'rice_names.name as rice_name',
                DB::raw('COALESCE(rice_forms.form_name, rfm3.name) as rice_form_name')
            ]);

        if (!empty($from)) {
            $fromDate = Carbon::parse($from)->startOfDay();
            $query->where('live_prices.created_at', '>=', $fromDate);
        }
        if (!empty($to)) {
            $toDate = Carbon::parse($to)->endOfDay();
            $query->where('live_prices.created_at', '<=', $toDate);
        }

        if (!empty($cropYear)) {
            $query->where('live_prices.cropYear', (int) $cropYear);
        }

        // Full export (CSV) with all matching rows, ignoring pagination
        if (!empty($export) && $export === 'csv') {
            @set_time_limit(0);

            $filename = 'live_prices_' . ($from ?? 'start') . '_' . ($to ?? 'end') . ($cropYear ? '_'.$cropYear : '') . '.csv';

            // Stream in chunks so large date ranges do not exhaust memory
            $exportQuery = $query->clone()->select([
                'live_prices.id',
                'live_prices.created_at',
                'live_prices.cropYear',
                'live_prices.min_price',
                'live_prices.max_price',
                'live_prices.opening',
                'live_prices.closing',
                'rice_names.name as rice_name',
                DB::raw('COALESCE(rice_forms.form_name, rfm3.name) as rice_form_name'),
            ]);

            return response()->streamDownload(function () use ($exportQuery) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Rice Name', 'Rice Form', 'Date', 'Crop Year', 'Min Price', 'Max Price', 'Opening', 'Closing'], ',', '"', '\\');

                $exportQuery->chunkById(1000, function ($rows) use ($out) {
                    foreach ($rows as $r) {
                        fputcsv($out, [
                            (string) ($r->rice_name ?? ''),
                            (string) ($r->rice_form_name ?? ''),
                            $r->created_at ? Carbon::parse($r->created_at)->format('Y-m-d') : '',
                            $r->cropYear ?? '',
                            $r->min_price ?? '',
                            $r->max_price ?? '',
                            $r->opening ?? '',
                            $r->closing ?? '',
                        ], ',', '"', '\\');
                    }
                }, 'live_prices.id', 'id');

                fclose($out);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        // Paginated table for screen
        $rows = $query->orderBy('live_prices.created_at', 'desc')->paginate(50)->withQueryString();

        return view('reports.live_prices', [
            'rows' => $rows,
            'from' => $from,
            'to'   => $to,
            'cropYears' => $cropYears,
            'cropYear' => $cropYear
        ]);
    }
}
