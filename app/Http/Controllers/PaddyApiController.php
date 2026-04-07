<?php

namespace App\Http\Controllers;

use App\PaddyMandiModel;
use App\PaddyPrice;
use App\PaddyStateModel;
use App\RiceName;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaddyApiController extends Controller
{
    /**
     * Calendar date (Y-m-d) of the most recently inserted/updated paddy row.
     * Uses raw latest row — not filtered by price, so rows with "----" or 0 still anchor the snapshot.
     */
    private function latestPaddyPricesDate(): ?string
    {
        $row = PaddyPrice::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        return $row ? Carbon::parse($row->created_at)->format('Y-m-d') : null;
    }

    public function listPaddy(Request $request)
    {
        $lastAddedDate = $this->latestPaddyPricesDate();

        $selectedStatesIds = [];
        if ($lastAddedDate !== null) {
            $selectedStatesIds = array_unique(
                PaddyPrice::query()
                    ->whereDate('created_at', $lastAddedDate)
                    ->pluck('state')
                    ->filter()
                    ->values()
                    ->toArray()
            );
        }

        $paddyState = $selectedStatesIds === []
            ? collect()
            : PaddyStateModel::query()
                ->select('id', 'state')
                ->where('status', 1)
                ->whereIn('id', $selectedStatesIds)
                ->get();

        return response()->json([
            'status' => true,
            'message' => 'Paddy state successfully',
            'lastSnapshotDate' => $lastAddedDate,
            'data' => $paddyState,
        ]);
    }

    public function listPaddyMandi($stateId)
    {
        $lastAddedDate = $this->latestPaddyPricesDate();

        $selectedMandiIds = [];
        if ($lastAddedDate !== null) {
            $selectedMandiIds = array_unique(
                PaddyPrice::query()
                    ->where('state', $stateId)
                    ->whereDate('created_at', $lastAddedDate)
                    ->pluck('mandi')
                    ->filter()
                    ->values()
                    ->toArray()
            );
        }

        $paddyMandi = $selectedMandiIds === []
            ? collect()
            : PaddyMandiModel::query()
                ->select('id', 'mandi', 'state_id')
                ->where('state_id', $stateId)
                ->whereIn('id', $selectedMandiIds)
                ->where('status', 1)
                ->get();

        return response()->json([
            'status' => true,
            'message' => 'Paddy mandi successfully',
            'lastSnapshotDate' => $lastAddedDate,
            'data' => $paddyMandi,
        ]);
    }

    public function getPaddyPrices($mandi_id, $state_id)
    {
        $lastEnterDate = $this->latestPaddyPricesDate();
        $lastCreated_at = '';

        $paddyPrices = collect();

        if ($lastEnterDate !== null) {
            $anchorRow = PaddyPrice::query()
                ->whereDate('created_at', $lastEnterDate)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->first();
            if ($anchorRow) {
                $lastCreated_at = $anchorRow->created_at->format('Y-m-d H:i');
            }

            $paddyPrices = PaddyPrice::query()
                ->where('mandi', $mandi_id)
                ->where('state', $state_id)
                ->whereDate('created_at', $lastEnterDate)
                ->with(['getMandi_rel:id,mandi', 'getState_rel:id,state', 'quality_rel:id,name'])
                ->orderByDesc('id')
                ->get()
                ->groupBy(fn ($item) => $item->created_at->format('Y-m-d'))
                ->map(function ($group) {
                    return $group->groupBy('quality_id')->map(fn ($qGroup) => $qGroup->first());
                })
                ->first() ?? collect();
        }

        return response()->json([
            'status' => true,
            'message' => 'Paddy get successfully',
            'data' => $paddyPrices,
            'lastUpdatedDate' => $lastCreated_at,
            'lastSnapshotDate' => $lastEnterDate,
        ]);
    }

    public function getPaddyPricesByPaddy($stateId, $paddyId)
    {
        $lastEnterDate = $this->latestPaddyPricesDate();
        $lastCreated_at = '';

        $paddyPrices = collect();

        if ($lastEnterDate !== null) {
            $anchorRow = PaddyPrice::query()
                ->whereDate('created_at', $lastEnterDate)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->first();
            if ($anchorRow) {
                $lastCreated_at = $anchorRow->created_at->format('Y-m-d H:i');
            }

            $paddyPrices = PaddyPrice::query()
                ->where('quality_id', $paddyId)
                ->where('state', $stateId)
                ->whereDate('created_at', $lastEnterDate)
                ->with(['getMandi_rel:id,mandi', 'getState_rel:id,state', 'quality_rel:id,name'])
                ->orderByDesc('id')
                ->get()
                ->groupBy(fn ($item) => $item->created_at->format('Y-m-d'))
                ->map(function ($group) {
                    return $group->groupBy('quality_id')->map(fn ($qGroup) => $qGroup);
                })
                ->first() ?? collect();
        }

        return response()->json([
            'status' => true,
            'message' => 'Paddy get successfully',
            'data' => $paddyPrices,
            'lastUpdatedDate' => $lastCreated_at,
            'lastSnapshotDate' => $lastEnterDate,
        ]);
    }

    public function getPaddyQualities($stateId)
    {
        $lastEnterDate = $this->latestPaddyPricesDate();
        $qualities = collect();

        if ($lastEnterDate !== null) {
            $paddyQualityIds = PaddyPrice::query()
                ->where('state', $stateId)
                ->whereDate('created_at', $lastEnterDate)
                ->orderByDesc('id')
                ->pluck('quality_id')
                ->toArray();

            $paddyQualityArrayIds = array_values(array_unique(array_filter($paddyQualityIds)));

            if ($paddyQualityArrayIds !== []) {
                $qualities = RiceName::query()
                    ->select('id', 'name')
                    ->whereIn('id', $paddyQualityArrayIds)
                    ->get();
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Paddy mandi get successfully',
            'lastSnapshotDate' => $lastEnterDate,
            'data' => $qualities,
        ]);
    }

    public function GetPaddyMapData($mandi_id, $state_id, $quality_id)
    {
        $lastEnterDate = $this->latestPaddyPricesDate();

        $paddyPricePre = PaddyPrice::query()
            ->where('mandi', $mandi_id)
            ->where('state', $state_id)
            ->where('quality_id', $quality_id)
            ->orderBy('created_at');

        $hand_cutting_price = (clone $paddyPricePre)
            ->where('hand_cutting_price', '!=', '----')
            ->pluck('hand_cutting_price', 'created_at')
            ->map(function ($q) {
                return (int) ((str_contains((string) $q, '-')) ? explode('-', (string) $q)[1] : $q);
            });

        $machine_cutting_price = (clone $paddyPricePre)
            ->where('machine_cutting_price', '!=', '----')
            ->pluck('machine_cutting_price', 'created_at')
            ->map(function ($q) {
                return (int) ((str_contains((string) $q, '-')) ? explode('-', (string) $q)[1] : $q);
            });

        return response()->json([
            'status' => true,
            'message' => 'Paddy get successfully',
            'lastSnapshotDate' => $lastEnterDate,
            'data' => [
                'hand_cutting_price' => $hand_cutting_price,
                'machine_cutting_price' => $machine_cutting_price,
            ],
        ]);
    }
}

