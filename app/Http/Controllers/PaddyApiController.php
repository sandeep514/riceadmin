<?php

namespace App\Http\Controllers;

use App\PaddyMandiModel;
use App\PaddyPrice;
use App\PaddyQuality;
use App\PaddySellQuery;
use App\PaddyStateModel;
use App\PaddyTrade;
use App\PaddyTradeCurrentStatus;
use App\PaddyTradeInterested;
use App\RiceName;
use App\SellerPackingINR;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
     * - packing_id (optional; sellerPackingINR id). Legacy key "packing" accepted as id.
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

        // Prefer packing_id; fall back to packing when it is a numeric id
        if (! $request->filled('packing_id') && $request->filled('packing') && is_numeric($request->input('packing'))) {
            $request->merge(['packing_id' => (int) $request->input('packing')]);
        }

        $validator = Validator::make($request->all(), [
            'category' => 'required|in:basmati,non-basmati',
            'quality' => 'required|integer|exists:paddy_qualities,id',
            'qualityName' => 'nullable|string|max:255',
            'hand_combined' => 'required|string|max:100',
            'packing_id' => 'nullable|integer|exists:sellerPackingINR,id',
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

        $packingId = $request->filled('packing_id') ? (int) $request->packing_id : null;
        $packingLabel = null;
        if ($packingId) {
            $packingLabel = optional(SellerPackingINR::find($packingId))->packing;
        }

        $row = PaddySellQuery::create([
            'category' => $request->category,
            'quality' => (int) $request->quality,
            'quality_name' => $qualityName,
            'hand_combined' => $request->hand_combined,
            'packing_id' => $packingId,
            'packing' => $packingLabel,
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

        $row->load('packingRel');

        $this->sendPaddySellQueryEnquiryMail($row);

        return response()->json([
            'status' => true,
            'message' => 'Paddy sell query submitted successfully.',
            'data' => [
                'id' => $row->id,
                'category' => $row->category,
                'quality' => $row->quality,
                'qualityName' => $row->quality_name,
                'hand_combined' => $row->hand_combined,
                'packing_id' => $row->packing_id,
                'packing' => $row->packing_label !== '-' ? $row->packing_label : null,
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

    /**
     * Notify enquiry mailbox when a new paddy sell query is submitted.
     */
    private function sendPaddySellQueryEnquiryMail(PaddySellQuery $row): void
    {
        try {
            $user = User::query()
                ->where('id', (int) $row->user_id)
                ->first(['name', 'email', 'phone', 'mobile']);

            $phone = $user ? ($user->phone ?: $user->mobile) : null;

            $mailPayload = [
                'creator_name' => $user->name ?? '—',
                'creator_email' => $user->email ?? '—',
                'creator_phone' => $phone ?: '—',
                'query_id' => $row->id,
                'category' => $row->category,
                'category_label' => $row->category_label,
                'quality_name' => $row->quality_name ?: optional($row->paddyQuality)->quality,
                'hand_combined' => $row->hand_combined,
                'packing' => $row->packing_label !== '-' ? $row->packing_label : '—',
                'quantity' => $row->quantity,
                'rate' => $row->rate,
                'valid_days' => $row->valid_days,
                'location' => $row->location,
                'contact_person' => $row->contact_person,
                'contact_number' => $row->contact_number,
                'type' => $row->type,
                'image_url' => $row->image_url,
            ];

            $mailTo = 'enquiry@sntcgroup.com';
            $subject = 'New Paddy Sell Query #' . $row->id;
            $mailFrom = 'info@sntcgroup.com';
            $mailFromName = 'SNTC Team - India';

            Mail::send('mail.PaddySellQueryReceived', $mailPayload, function ($message) use ($mailTo, $subject, $mailFrom, $mailFromName) {
                $message->to($mailTo)->subject($subject);
                $message->from($mailFrom, $mailFromName);
            });
        } catch (\Throwable $e) {
            // Do not fail the API if mail fails
            Log::error('Paddy sell query enquiry mail failed: ' . $e->getMessage(), [
                'paddy_sell_query_id' => $row->id ?? null,
            ]);
        }
    }

    /**
     * List paddy trades for app & web portal (paginated).
     *
     * Default statuses (UI shows badge per trade):
     * - 1 Active, 4 In-Process, 12 Hold, 3 Sold
     * Deactivated (5) is excluded from default list; use status=5 or status=all.
     *
     * Optional query params:
     * - category: basmati | non-basmati
     * - quality: paddy_qualities id
     * - packing_id: seller packing id
     * - userId | user_id: logged-in user (for per-trade is_interested / already_interested)
     * - seller_user_id: filter trades by original seller user id
     * - status: single status code, or comma list (e.g. 1,4,12,3), or "all"
     * - page: default 1
     * - per_page | perPage | limit: default 15, max 100
     */
    public function listPaddyTrades(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'nullable|in:basmati,non-basmati',
            'quality' => 'nullable|integer|exists:paddy_qualities,id',
            'packing_id' => 'nullable|integer|exists:sellerPackingINR,id',
            'userId' => 'nullable|integer|exists:users,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'seller_user_id' => 'nullable|integer|exists:users,id',
            'status' => 'nullable',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'perPage' => 'nullable|integer|min:1|max:100',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $perPage = (int) (
            $request->input('per_page')
            ?? $request->input('perPage')
            ?? $request->input('limit')
            ?? 15
        );
        $perPage = max(1, min(100, $perPage));
        $page = max(1, (int) $request->input('page', 1));

        $interestUserId = $this->resolveInterestUserId($request);

        // Default portal list (not Deactivated)
        $listableStatuses = PaddyTrade::$listableStatuses;

        $query = PaddyTrade::query()
            ->with([
                'paddyQuality:id,quality,type',
                'packingRel:id,packing',
                'user:id,name,email,phone',
            ])
            ->orderByRaw('FIELD(status, 1, 4, 12, 3, 5)')
            ->orderByDesc('id');

        $statusParam = $request->input('status');
        if ($statusParam === null || $statusParam === '') {
            $query->whereIn('status', $listableStatuses);
        } elseif ($statusParam !== 'all') {
            $statusIds = collect(explode(',', (string) $statusParam))
                ->map(fn ($s) => (int) trim($s))
                ->filter(fn ($s) => $s > 0)
                ->unique()
                ->values()
                ->all();

            if ($statusIds !== []) {
                $query->whereIn('status', $statusIds);
            } else {
                $query->whereIn('status', $listableStatuses);
            }
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('quality')) {
            $query->where('quality', (int) $request->quality);
        }
        if ($request->filled('packing_id')) {
            $query->where('packing_id', (int) $request->packing_id);
        }
        if ($request->filled('seller_user_id')) {
            $query->where('user_id', (int) $request->seller_user_id);
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $interestedTradeIds = [];
        if ($interestUserId) {
            $pageTradeIds = collect($paginator->items())->pluck('id')->filter()->values()->all();
            if ($pageTradeIds !== []) {
                $interestedTradeIds = PaddyTradeInterested::query()
                    ->where('user_id', $interestUserId)
                    ->where('status', 1)
                    ->whereIn('paddy_trade_id', $pageTradeIds)
                    ->pluck('paddy_trade_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }
        }

        $data = collect($paginator->items())->map(function ($row) use ($interestUserId, $interestedTradeIds) {
            $isInterested = $interestUserId
                ? in_array((int) $row->id, $interestedTradeIds, true)
                : false;

            return $this->formatPaddyTradeResponse($row, $isInterested, $interestUserId);
        })->values();

        $marketStatus = PaddyTradeCurrentStatus::current();

        return response()->json([
            'status' => true,
            'message' => 'Paddy trades list',
            'data' => $data,
            'userId' => $interestUserId,
            'currentStatus' => (int) $marketStatus->currentStatus,
            'statusMessage' => $marketStatus->message,
            'market_status' => [
                'currentStatus' => (int) $marketStatus->currentStatus,
                'label' => $marketStatus->status_label,
                'message' => $marketStatus->message,
            ],
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ], 200);
    }

    /**
     * Single paddy trade detail for app & web portal.
     * Optional query: userId | user_id — for is_interested flag.
     */
    public function getPaddyTradeDetail(Request $request, $id)
    {
        $trade = PaddyTrade::query()
            ->with([
                'paddyQuality:id,quality,type',
                'packingRel:id,packing',
                'user:id,name,email,phone',
            ])
            ->find($id);

        if (! $trade) {
            return response()->json([
                'status' => false,
                'message' => 'Paddy trade not found',
            ], 404);
        }

        $interestUserId = $this->resolveInterestUserId($request);
        $isInterested = false;
        if ($interestUserId) {
            $isInterested = PaddyTradeInterested::query()
                ->where('paddy_trade_id', $trade->id)
                ->where('user_id', $interestUserId)
                ->where('status', 1)
                ->exists();
        }

        $marketStatus = PaddyTradeCurrentStatus::current();

        return response()->json([
            'status' => true,
            'message' => 'Paddy trade details',
            'data' => $this->formatPaddyTradeResponse($trade, $isInterested, $interestUserId),
            'userId' => $interestUserId,
            'currentStatus' => (int) $marketStatus->currentStatus,
            'statusMessage' => $marketStatus->message,
            'market_status' => [
                'currentStatus' => (int) $marketStatus->currentStatus,
                'label' => $marketStatus->status_label,
                'message' => $marketStatus->message,
            ],
        ], 200);
    }

    /**
     * Resolve logged-in user id for interest flags (userId preferred, then user_id).
     */
    private function resolveInterestUserId(Request $request): ?int
    {
        $value = $request->input('userId', $request->input('user_id'));
        if ($value === null || $value === '') {
            return null;
        }

        $id = (int) $value;

        return $id > 0 ? $id : null;
    }

    /**
     * Show interest on a paddy trade (app & web).
     *
     * Payload:
     * - paddy_trade_id | tradeId | trade_id (required)
     * - user_id | userId (required)
     */
    public function showPaddyTradeInterest(Request $request)
    {
        if (! $request->filled('paddy_trade_id')) {
            if ($request->filled('tradeId')) {
                $request->merge(['paddy_trade_id' => $request->input('tradeId')]);
            } elseif ($request->filled('trade_id')) {
                $request->merge(['paddy_trade_id' => $request->input('trade_id')]);
            }
        }
        if (! $request->filled('user_id') && $request->filled('userId')) {
            $request->merge(['user_id' => $request->input('userId')]);
        }

        $validator = Validator::make($request->all(), [
            'paddy_trade_id' => 'required|integer|exists:paddy_trades,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tradeId = (int) $request->paddy_trade_id;
        $userId = (int) $request->user_id;

        $trade = PaddyTrade::query()
            ->with(['paddyQuality:id,quality,type', 'packingRel:id,packing'])
            ->find($tradeId);

        if (! $trade) {
            return response()->json([
                'status' => false,
                'message' => 'Paddy trade not found',
            ], 404);
        }

        if ((int) $trade->status !== 1) {
            $statusLabel = $trade->status_label;
            return response()->json([
                'status' => false,
                'message' => (int) $trade->status === 5
                    ? 'This paddy trade is deactivated and not available for interest'
                    : 'This paddy trade is not available for interest (status: ' . $statusLabel . ')',
            ], 422);
        }

        $marketStatus = PaddyTradeCurrentStatus::current();
        $marketCode = (int) $marketStatus->currentStatus;
        if ($marketCode !== 1) {
            $msg = $marketCode === 12
                ? 'Paddy market is on hold. Interest cannot be submitted right now.'
                : 'Paddy market is closed. Interest cannot be submitted right now.';

            return response()->json([
                'status' => false,
                'message' => $msg,
                'currentStatus' => $marketCode,
                'statusMessage' => $marketStatus->message,
            ], 422);
        }

        $existing = PaddyTradeInterested::query()
            ->where('paddy_trade_id', $tradeId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => true,
                'message' => 'Interest already registered for this paddy trade',
                'data' => [
                    'id' => $existing->id,
                    'paddy_trade_id' => $existing->paddy_trade_id,
                    'user_id' => $existing->user_id,
                    'already_interested' => true,
                ],
            ], 200);
        }

        $interest = PaddyTradeInterested::create([
            'paddy_trade_id' => $tradeId,
            'user_id' => $userId,
            'status' => 1,
        ]);

        $this->sendPaddyTradeInterestEnquiryMail($trade, $userId);

        return response()->json([
            'status' => true,
            'message' => 'Interest submitted successfully',
            'data' => [
                'id' => $interest->id,
                'paddy_trade_id' => $interest->paddy_trade_id,
                'user_id' => $interest->user_id,
                'already_interested' => false,
            ],
        ], 200);
    }

    /**
     * Notify enquiry mailbox when a user shows interest on a paddy trade.
     */
    private function sendPaddyTradeInterestEnquiryMail(PaddyTrade $trade, int $userId): void
    {
        try {
            $user = User::query()
                ->where('id', $userId)
                ->first(['id', 'name', 'email', 'phone', 'mobile', 'companyname']);

            $phone = $user ? ($user->phone ?: $user->mobile) : null;
            $packingLabel = $trade->packing_label;

            $mailPayload = [
                'username' => $user->name ?? '—',
                'email' => $user->email ?? '—',
                'mobile' => $phone ?: '—',
                'companyName' => $user->companyname ?? '—',
                'userId' => $userId,
                'tradeId' => $trade->id,
                'category' => $trade->category,
                'category_label' => $trade->category_label,
                'qualityName' => $trade->quality_name ?: optional($trade->paddyQuality)->quality,
                'hand_combined' => $trade->hand_combined,
                'packing' => $packingLabel !== '-' ? $packingLabel : '—',
                'quantity' => $trade->quantity,
                'rate' => $trade->rate,
                'validDays' => $trade->valid_days,
                'location' => $trade->location,
                'contactperson' => $trade->contact_person,
                'contactNumber' => $trade->contact_number,
                'imageUrl' => $trade->image_url,
            ];

            $mailTo = 'enquiry@sntcgroup.com';
            $subject = 'Paddy Trade Interest - PaddyTrade_' . $trade->id;
            $mailFrom = 'info@sntcgroup.com';
            $mailFromName = 'SNTC Team - India';

            Mail::send('mail.PaddyTradeInterest', $mailPayload, function ($message) use ($mailTo, $subject, $mailFrom, $mailFromName) {
                $message->to($mailTo)->subject($subject);
                $message->from($mailFrom, $mailFromName);
            });
        } catch (\Throwable $e) {
            Log::error('Paddy trade interest enquiry mail failed: ' . $e->getMessage(), [
                'paddy_trade_id' => $trade->id ?? null,
                'user_id' => $userId,
            ]);
        }
    }

    /**
     * Normalize paddy trade payload for mobile/web clients.
     */
    private function formatPaddyTradeResponse(PaddyTrade $row, bool $isInterested = false, ?int $interestUserId = null): array
    {
        $packingLabel = $row->packing_label;

        return [
            'id' => $row->id,
            'paddy_sell_query_id' => $row->paddy_sell_query_id,
            'category' => $row->category,
            'category_label' => $row->category_label,
            'quality' => $row->quality,
            'qualityName' => $row->quality_name ?: optional($row->paddyQuality)->quality,
            'hand_combined' => $row->hand_combined,
            'packing_id' => $row->packing_id,
            'packing' => $packingLabel !== '-' ? $packingLabel : null,
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
            'user' => $row->user ? [
                'id' => $row->user->id,
                'name' => $row->user->name,
                'email' => $row->user->email,
                'phone' => $row->user->phone ?? null,
            ] : null,
            'is_interested' => $isInterested,
            'already_interested' => $isInterested,
            'interest_user_id' => $interestUserId,
            'remarks' => $row->remarks,
            'status' => $row->status,
            'status_label' => $row->status_label,
            'sold_at_amount' => $row->sold_at_amount,
            'sold_at' => $row->sold_at,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ];
    }
}

