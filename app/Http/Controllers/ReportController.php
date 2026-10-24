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
        $nameId = $request->get('name');
        $formId = $request->get('form');
        $state = $request->get('state');
        $hideEmptyPrices = $request->boolean('hide_empty_prices');
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

        $this->applyLivePriceReportFilters($query, $from, $to, $cropYear, $nameId, $formId, $state, $hideEmptyPrices, 'live_prices');

        // One last-created row per name + form + state + crop year + date (screen + export).
        $this->applyLatestPerDayScope($query, $hideEmptyPrices);

        // Full export (CSV) with filtered latest rows, ignoring pagination
        if (!empty($export) && $export === 'csv') {
            @set_time_limit(0);

            $filename = 'live_prices_' . ($from ?? 'start') . '_' . ($to ?? 'end') . ($cropYear ? '_'.$cropYear : '') . '.csv';

            $exportQuery = $query->clone()->select([
                'live_prices.id',
                'live_prices.state',
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
                fputcsv($out, ['Rice Name', 'Rice Form', 'State', 'Date', 'Crop Year', 'Min Price', 'Max Price', 'Opening', 'Closing'], ',', '"', '\\');

                $exportQuery->chunkById(1000, function ($rows) use ($out) {
                    foreach ($rows as $r) {
                        fputcsv($out, [
                            (string) ($r->rice_name ?? ''),
                            (string) ($r->rice_form_name ?? ''),
                            (string) ($r->state ?? ''),
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

        $riceNames = LivePrice::query()
            ->join('rice_names', 'live_prices.name', '=', 'rice_names.id')
            ->where('live_prices.name', '>', 0)
            ->select('rice_names.id', 'rice_names.name')
            ->distinct()
            ->orderBy('rice_names.name')
            ->pluck('rice_names.name', 'rice_names.id');

        $riceForms = LivePrice::query()
            ->leftJoin('rice_forms', 'live_prices.form', '=', 'rice_forms.id')
            ->leftJoin('rice_form_milestone3 as rfm3', 'live_prices.form', '=', 'rfm3.id')
            ->where('live_prices.form', '>', 0)
            ->select([
                'live_prices.form as id',
                DB::raw('COALESCE(rice_forms.form_name, rfm3.name) as form_name'),
            ])
            ->distinct()
            ->orderBy('form_name')
            ->get()
            ->filter(function ($row) {
                return ! empty($row->form_name);
            })
            ->pluck('form_name', 'id');

        $states = LivePrice::query()
            ->whereNotNull('state')
            ->where('state', '!=', '')
            ->select('state')
            ->distinct()
            ->orderBy('state')
            ->pluck('state');

        return view('reports.live_prices', [
            'rows' => $rows,
            'from' => $from,
            'to'   => $to,
            'cropYears' => $cropYears,
            'cropYear' => $cropYear,
            'riceNames' => $riceNames,
            'riceForms' => $riceForms,
            'states' => $states,
            'nameId' => $nameId,
            'formId' => $formId,
            'state' => $state,
            'hideEmptyPrices' => $hideEmptyPrices,
        ]);
    }

    private function applyLivePriceReportFilters($query, $from, $to, $cropYear, $nameId, $formId, $state, bool $hideEmptyPrices, string $table = 'live_prices')
    {
        $prefix = $table !== '' ? $table.'.' : '';

        if (!empty($from)) {
            $query->where($prefix.'created_at', '>=', LivePrice::istDateTime(LivePrice::dayStart($from)));
        }
        if (!empty($to)) {
            $query->where($prefix.'created_at', '<', LivePrice::istDateTime(LivePrice::dayStart($to)->addDay()));
        }
        if (!empty($cropYear)) {
            $query->where($prefix.'cropYear', (int) $cropYear);
        }
        if (!empty($nameId)) {
            $query->where($prefix.'name', (int) $nameId);
        }
        if (!empty($formId)) {
            $query->where($prefix.'form', (int) $formId);
        }
        if ($state !== null && $state !== '') {
            $query->where($prefix.'state', $state);
        }
        if ($hideEmptyPrices) {
            $minCol = $prefix.'min_price';
            $maxCol = $prefix.'max_price';
            $query->whereRaw("CAST(COALESCE(NULLIF(TRIM({$minCol}), ''), '0') AS DECIMAL(18,4)) > 0")
                ->whereRaw("CAST(COALESCE(NULLIF(TRIM({$maxCol}), ''), '0') AS DECIMAL(18,4)) > 0");
        }
    }

    /**
     * Scope: keep ONLY the last entry of each calendar date per
     * name + form + state + crop year group (last = highest id; ids are
     * monotonic per save, so saved order beats created_at ties).
     * Applied directly to the main query so the screen and the CSV export
     * share one code path.
     */
    private function applyLatestPerDayScope($query, bool $hideEmptyPrices): void
    {
        $table = 'live_prices';
        $lp = $table;

        $query->whereRaw(
            "NOT EXISTS (
                SELECT 1 FROM {$table} newer
                WHERE newer.name = {$lp}.name
                  AND newer.form = {$lp}.form
                  AND ((newer.state IS NULL AND {$lp}.state IS NULL) OR newer.state = {$lp}.state)
                  AND ((newer.cropYear IS NULL AND {$lp}.cropYear IS NULL) OR newer.cropYear = {$lp}.cropYear)
                  AND DATE(newer.created_at) = DATE({$lp}.created_at)
                  AND newer.id > {$lp}.id
            )"
        );

        if ($hideEmptyPrices) {
            $query->whereRaw("CAST(COALESCE(NULLIF(TRIM({$lp}.min_price), ''), '0') AS DECIMAL(18,4)) > 0")
                ->whereRaw("CAST(COALESCE(NULLIF(TRIM({$lp}.max_price), ''), '0') AS DECIMAL(18,4)) > 0");
        }
    }
}
