<?php

namespace App\Http\Controllers;

use App\PaddyMandiModel;
use App\PaddyPrice;
use App\PaddyQuality;
use App\PaddySellQuery;
use App\PaddyStateModel;
use App\RiceName;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
                ->orderByRaw('order_no IS NULL, order_no ASC')
                ->orderBy('id')
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
                ->orderByRaw('order_no IS NULL, order_no ASC')
                ->orderBy('id')
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

    /**
     * List available (active) paddy qualities from master.
     *
     * Optional query params:
     * - type: basmati | non-basmati
     */
    public function listPaddyQualities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|in:basmati,non-basmati',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = PaddyQuality::query()
            ->select(['id', 'type', 'quality', 'description', 'order'])
            ->where('status', 1)
            ->orderByRaw('`order` IS NULL, `order` ASC')
            ->orderBy('id');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $qualities = $query->get()->map(function ($row) {
            return [
                'id' => $row->id,
                'type' => $row->type,
                'type_label' => $row->type_label,
                'quality' => $row->quality,
                'description' => $row->description,
                'order' => $row->order,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Paddy qualities list',
            'data' => $qualities,
        ], 200);
    }

    /**
     * Submit paddy sell query (web/app).
     *
     * Expected fields (multipart/form-data or JSON):
     * - category (basmati|non-basmati)
     * - quality (paddy_qualities id)
     * - qualityName (optional display name)
     * - hand_combined
     * - packing (optional)
     * - contactNumber (required; legacy key "contact" also accepted)
     * - contactperson
     * - image (optional file)
     * - location
     * - quantity
     * - rate
     * - type (web|app)
     * - userId
     * - validDays
     */
    public function submitPaddySellQuery(Request $request)
    {
        // Prefer contactNumber; fall back to legacy "contact"
        if (! $request->filled('contactNumber') && $request->filled('contact')) {
            $request->merge(['contactNumber' => $request->input('contact')]);
        }

        $validator = Validator::make($request->all(), [
            'category' => 'required|in:basmati,non-basmati',
            'quality' => 'required|integer|exists:paddy_qualities,id',
            'qualityName' => 'nullable|string|max:255',
            'hand_combined' => 'required|string|max:100',
            'packing' => 'nullable|string|max:255',
            'contactNumber' => 'required|string|max:50',
            'contactperson' => 'required|string|max:255',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'location' => 'required|string|max:255',
            'quantity' => 'required|string|max:100',
            'rate' => 'required|string|max:100',
            'type' => 'nullable|string|max:50',
            'userId' => 'required|integer|exists:users,id',
            'validDays' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $imageName = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $uploadDir = public_path('uploads');
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $imageName = 'paddy_sell_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $imageName);
        }

        $qualityName = $request->input('qualityName');
        if (! $qualityName) {
            $qualityName = optional(PaddyQuality::find($request->quality))->quality;
        }

        $row = PaddySellQuery::create([
            'category' => $request->category,
            'quality' => (int) $request->quality,
            'quality_name' => $qualityName,
            'hand_combined' => $request->hand_combined,
            'packing' => $request->input('packing'),
            'contact_number' => $request->contactNumber,
            'contact_person' => $request->contactperson,
            'image' => $imageName,
            'location' => $request->location,
            'quantity' => $request->quantity,
            'rate' => $request->rate,
            'valid_days' => $request->validDays,
            'type' => $request->input('type', 'web'),
            'user_id' => (int) $request->userId,
            'status' => 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Paddy sell query submitted successfully.',
            'data' => [
                'id' => $row->id,
                'category' => $row->category,
                'quality' => $row->quality,
                'qualityName' => $row->quality_name,
                'hand_combined' => $row->hand_combined,
                'packing' => $row->packing,
                'contactNumber' => $row->contact_number,
                'contactperson' => $row->contact_person,
                'image' => $row->image,
                'imageUrl' => $row->image_url,
                'location' => $row->location,
                'quantity' => $row->quantity,
                'rate' => $row->rate,
                'validDays' => $row->valid_days,
                'type' => $row->type,
                'userId' => $row->user_id,
                'status' => $row->status,
                'created_at' => $row->created_at,
            ],
        ], 200);
    }
}

