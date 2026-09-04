<?php

namespace App\Http\Controllers;

use App\LivePrice;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Public (no-auth) live rice price APIs for 3rd-party consumers.
 */
class PublicLivePriceController extends Controller
{
    /**
     * Latest calendar day's live prices, nested as:
     * state → riceType (basmati / non-basmati) → date → riceName → form → price fields
     *
     * Optional query params:
     * - state (exact match, e.g. PUNJAB-HARYANA)
     * - riceType (basmati | non-basmati)
     * - year (cropYear filter)
     */
    public function latest(Request $request)
    {
        $stateFilter = $request->filled('state') ? trim((string) $request->input('state')) : null;
        $riceTypeFilter = $request->filled('riceType')
            ? strtolower(trim((string) $request->input('riceType')))
            : null;
        $cropYear = $request->filled('year') ? trim((string) $request->input('year')) : null;

        if ($riceTypeFilter !== null) {
            $riceTypeFilter = str_replace([' ', '_'], '-', $riceTypeFilter);
            if (! in_array($riceTypeFilter, ['basmati', 'non-basmati'], true)) {
                return response()->json([
                    'status' => false,
                    'message' => 'riceType must be basmati or non-basmati.',
                    'data' => (object) [],
                ], 422);
            }
        }

        $baseQuery = LivePrice::query()
            ->where('name', '!=', '0')
            ->where('form', '!=', '0')
            ->whereNotNull('min_price')
            ->whereNotNull('max_price')
            ->when($stateFilter, fn ($q) => $q->where('state', $stateFilter))
            ->when($cropYear !== null && $cropYear !== '', function ($q) use ($cropYear) {
                $q->where(function ($inner) use ($cropYear) {
                    $inner->where('cropYear', $cropYear);
                    if (preg_match('/^\d{4}$/', $cropYear)) {
                        $inner->orWhere('cropYear', 'like', $cropYear . '-%')
                            ->orWhere('cropYear', 'like', $cropYear . '/%');
                    }
                });
            });

        $lastRecord = (clone $baseQuery)->orderByDesc('id')->first();

        if (! $lastRecord || ! $lastRecord->created_at) {
            return response()->json([
                'status' => true,
                'message' => 'No live prices found.',
                'date' => null,
                'data' => (object) [],
            ]);
        }

        $latestAt = Carbon::parse($lastRecord->created_at)
            ->timezone(config('app.timezone', 'Asia/Kolkata'));
        $latestDate = $latestAt->format('Y-m-d');
        $latestDateTime = $latestAt->format('Y-m-d H:i:s');

        $rows = LivePrice::query()
            ->with([
                'name_rel:id,name,type,order,status',
                'form_rel:id,form_name,type,order,status',
            ])
            ->join('rice_names as rn', 'rn.id', '=', 'live_prices.name')
            ->join('rice_forms as rf', 'rf.id', '=', 'live_prices.form')
            ->select('live_prices.*')
            ->where('live_prices.name', '!=', '0')
            ->where('live_prices.form', '!=', '0')
            ->whereNotNull('live_prices.min_price')
            ->whereNotNull('live_prices.max_price')
            ->whereDate('live_prices.created_at', $latestDate)
            ->when($stateFilter, fn ($q) => $q->where('live_prices.state', $stateFilter))
            ->when($cropYear !== null && $cropYear !== '', function ($q) use ($cropYear) {
                $q->where(function ($inner) use ($cropYear) {
                    $inner->where('live_prices.cropYear', $cropYear);
                    if (preg_match('/^\d{4}$/', $cropYear)) {
                        $inner->orWhere('live_prices.cropYear', 'like', $cropYear . '-%')
                            ->orWhere('live_prices.cropYear', 'like', $cropYear . '/%');
                    }
                });
            })
            ->when($riceTypeFilter, function ($q) use ($riceTypeFilter) {
                $q->where('rn.type', $riceTypeFilter)
                    ->where('rf.type', $riceTypeFilter);
            })
            ->where('rf.status', 1)
            ->orderByRaw('COALESCE(live_prices.state_order, 999999) ASC')
            ->orderBy('live_prices.state')
            ->orderByRaw('COALESCE(rn.`order`, 999999) ASC')
            ->orderBy('rn.name')
            ->orderByRaw('COALESCE(rf.`order`, 999999) ASC')
            ->orderBy('rf.form_name')
            ->orderByDesc('live_prices.id')
            ->get();

        // One row per state + rice + form for the latest date (keep last added / highest id).
        $rows = $rows
            ->filter(fn ($row) => $row->name_rel && $row->form_rel && $row->state)
            ->groupBy(fn ($row) => implode('|', [
                (string) $row->state,
                (string) $row->name,
                (string) $row->form,
            ]))
            ->map(fn ($group) => $group->sortByDesc('id')->first())
            ->values();

        $nested = [];

        foreach ($rows as $row) {
            $riceType = strtolower((string) ($row->name_rel->type ?? ''));
            $riceType = str_replace([' ', '_'], '-', $riceType);
            if ($riceType === '') {
                continue;
            }

            $state = (string) $row->state;
            $riceName = (string) $row->name_rel->name;
            $formName = (string) $row->form_rel->form_name;

            if (! isset($nested[$state])) {
                $nested[$state] = [];
            }
            if (! isset($nested[$state][$riceType])) {
                $nested[$state][$riceType] = [];
            }
            if (! isset($nested[$state][$riceType][$latestDateTime])) {
                $nested[$state][$riceType][$latestDateTime] = [];
            }
            if (! isset($nested[$state][$riceType][$latestDateTime][$riceName])) {
                $nested[$state][$riceType][$latestDateTime][$riceName] = [];
            }

            $nested[$state][$riceType][$latestDateTime][$riceName][$formName] = [
                'rice_name' => $riceName,
                'form' => $formName,
                'crop_year' => $row->cropYear,
                'min_price' => $row->min_price,
                'max_price' => $row->max_price,
                'up_down' => $row->up_down,
                'opening' => $row->opening,
            ];
        }

        // Prefer basmati before non-basmati under each state.
        foreach ($nested as $state => $byType) {
            uksort($byType, function ($a, $b) {
                $order = ['basmati' => 0, 'non-basmati' => 1];

                return ($order[$a] ?? 99) <=> ($order[$b] ?? 99) ?: strcmp((string) $a, (string) $b);
            });
            $nested[$state] = $byType;
        }

        return response()->json([
            'status' => true,
            'message' => 'Latest live rice prices.',
            'date' => $latestDateTime,
            'filters' => [
                'state' => $stateFilter,
                'riceType' => $riceTypeFilter,
                'year' => $cropYear,
            ],
            'data' => empty($nested) ? (object) [] : $nested,
        ]);
    }
}
