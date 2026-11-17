<?php

namespace App\Http\Controllers;

use App\Export\PaddyPricesApiExport;
use App\Jobs\ExportPaddyPricesMailJob;
use App\PaddyMandiModel;
use App\PaddyPrice;
use App\PaddyPriceDefault;
use App\PaddyQuality;
use App\PaddySellQuery;
use App\PaddyStateModel;
use App\PaddyTrade;
use App\PaddyTradeCurrentStatus;
use App\PaddyTradeInterested;
use App\Support\PaddyPriceSeedFilter;
use App\Support\QueuedMail;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class PaddyApiController extends Controller
{
    /**
     * IST calendar today + yesterday (Y-m-d). Used for per-crop/mandi fallback.
     *
     * @return array{0: string, 1: string}
     */
    private function paddyTodayYesterdayDates(): array
    {
        $today = Carbon::now('Asia/Kolkata')->toDateString();
        $yesterday = Carbon::now('Asia/Kolkata')->subDay()->toDateString();

        return [$today, $yesterday];
    }

    /**
     * Restrict a paddy price query to IST calendar today OR yesterday.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function scopePaddyTodayOrYesterday($query)
    {
        [$today, $yesterday] = $this->paddyTodayYesterdayDates();

        return $query->where(function ($q) use ($today, $yesterday) {
            $q->whereDate('created_at', $today)
                ->orWhereDate('created_at', $yesterday);
        });
    }

    /**
     * Resolve crop year: prefer requested year if it has today/yesterday rows;
     * otherwise fall back to all years (same empty-year behavior as before).
     *
     * @return array{0: ?string, 1: bool} [cropYear, hasWindowRows]
     */
    private function resolvePaddyCropYearForWindow($stateId, $mandiId, ?string $requestedCropYear): array
    {
        $cropYear = $requestedCropYear;
        $hasRows = $this->scopePaddyTodayOrYesterday(
            $this->paddyPriceBaseQuery($stateId, $mandiId, $cropYear)
        )->exists();

        if ($cropYear !== null && ! $hasRows) {
            $cropYear = null;
            $hasRows = $this->scopePaddyTodayOrYesterday(
                $this->paddyPriceBaseQuery($stateId, $mandiId, null)
            )->exists();
        }

        return [$cropYear, $hasRows];
    }

    /**
     * Among today+yesterday rows, keep one row per group key preferring today over yesterday
     * (latest id wins within the chosen day).
     *
     * @param  \Illuminate\Support\Collection<int, \App\PaddyPrice>  $rows
     * @return \Illuminate\Support\Collection<int, \App\PaddyPrice>
     */
    private function preferTodayElseYesterdayPerKey($rows, string $groupKey)
    {
        [$today, $yesterday] = $this->paddyTodayYesterdayDates();

        return $rows
            ->groupBy(fn ($row) => (string) $row->{$groupKey})
            ->map(function ($group) use ($today, $yesterday) {
                $byDay = $group->groupBy(function ($row) {
                    if (! $row->created_at) {
                        return '';
                    }

                    return Carbon::parse($row->created_at)
                        ->timezone('Asia/Kolkata')
                        ->format('Y-m-d');
                });

                $dayRows = $byDay->get($today) ?? $byDay->get($yesterday);
                if (! $dayRows || $dayRows->isEmpty()) {
                    return null;
                }

                return $dayRows->sortByDesc('id')->first();
            })
            ->filter()
            ->values();
    }

    /**
     * Primary snapshot date for meta: today if any today rows in the window collection, else yesterday.
     *
     * @param  \Illuminate\Support\Collection<int, \App\PaddyPrice>  $rows
     */
    private function snapshotDateFromWindowRows($rows): ?string
    {
        [$today, $yesterday] = $this->paddyTodayYesterdayDates();
        if ($rows->isEmpty()) {
            return null;
        }

        $hasToday = $rows->contains(function ($row) use ($today) {
            return $row->created_at
                && Carbon::parse($row->created_at)->timezone('Asia/Kolkata')->format('Y-m-d') === $today;
        });

        return $hasToday ? $today : $yesterday;
    }

    /**
     * Format lastUpdatedDate from max updated_at among selected rows.
     *
     * @param  \Illuminate\Support\Collection<int, \App\PaddyPrice>  $rows
     */
    private function lastUpdatedFromRows($rows): string
    {
        $anchor = $rows->sortByDesc(function ($row) {
            $t = $row->updated_at ?: $row->created_at;

            return $t ? Carbon::parse($t)->timestamp : 0;
        })->first();

        if (! $anchor) {
            return '';
        }

        $anchorTime = $anchor->updated_at ?: $anchor->created_at;

        return $anchorTime
            ? Carbon::parse($anchorTime)->timezone('Asia/Kolkata')->format('Y-m-d H:i')
            : '';
    }

    /**
     * @param  mixed  $stateId
     * @param  mixed  $mandiId
     * @param  mixed  $cropYear
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function paddyPriceBaseQuery($stateId = null, $mandiId = null, $cropYear = null)
    {
        return PaddyPrice::query()
            ->when($stateId !== null && $stateId !== '', fn ($q) => $q->where('state', (int) $stateId))
            ->when($mandiId !== null && $mandiId !== '', fn ($q) => $q->where('mandi', (int) $mandiId))
            ->when($this->normalizedCropYear($cropYear) !== null, function ($q) use ($cropYear) {
                $year = $this->normalizedCropYear($cropYear);
                $q->where(function ($inner) use ($year) {
                    // crop_year may be stored as int or string depending on insert path.
                    $inner->where('crop_year', $year)
                        ->orWhere('crop_year', (string) $year)
                        ->orWhere('crop_year', (int) $year);
                });
            });
    }

    private function normalizedCropYear($cropYear): ?string
    {
        if ($cropYear === null || $cropYear === '') {
            return null;
        }

        $year = trim((string) $cropYear);

        return $year === '' ? null : $year;
    }

    private function requestCropYear(Request $request): ?string
    {
        return $this->normalizedCropYear(
            $request->input('crop_year', $request->input('cropYear'))
        );
    }

    /**
     * Resolve state filter from state|state_id|stateId (numeric id preferred).
     */
    private function requestStateId(Request $request): ?int
    {
        $raw = $request->input(
            'state',
            $request->input('state_id', $request->input('stateId'))
        );
        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_numeric($raw)) {
            $id = (int) $raw;

            return $id > 0 ? $id : null;
        }

        $name = trim((string) $raw);
        if ($name === '') {
            return null;
        }

        $id = PaddyStateModel::query()
            ->where('status', 1)
            ->whereRaw('LOWER(state) = ?', [strtolower($name)])
            ->value('id');

        return $id ? (int) $id : null;
    }

    /**
     * List active paddy states.
     *
     * Visibility: states that have at least one price on IST calendar today
     * OR yesterday (per-crop style window). Crops older than yesterday are skipped.
     *
     * Optional: ?cropYear=2025 | ?crop_year=2025
     */
    public function listPaddy(Request $request)
    {
        [$cropYear, $hasRows] = $this->resolvePaddyCropYearForWindow(
            null,
            null,
            $this->requestCropYear($request)
        );

        $windowRows = collect();
        if ($hasRows) {
            $windowRows = $this->scopePaddyTodayOrYesterday(
                $this->paddyPriceBaseQuery(null, null, $cropYear)
            )->get(['id', 'state', 'created_at', 'updated_at']);
        }

        $selectedStatesIds = $windowRows
            ->pluck('state')
            ->filter()
            ->unique()
            ->values()
            ->all();

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
            'lastSnapshotDate' => $this->snapshotDateFromWindowRows($windowRows),
            'crop_year' => $cropYear,
            'data' => $paddyState,
        ]);
    }

    /**
     * Visibility: mandis in this state with at least one price on IST today OR yesterday.
     */
    public function listPaddyMandi(Request $request, $stateId)
    {
        [$cropYear, $hasRows] = $this->resolvePaddyCropYearForWindow(
            $stateId,
            null,
            $this->requestCropYear($request)
        );

        $windowRows = collect();
        if ($hasRows) {
            $windowRows = $this->scopePaddyTodayOrYesterday(
                $this->paddyPriceBaseQuery($stateId, null, $cropYear)
            )->get(['id', 'mandi', 'created_at', 'updated_at']);
        }

        $selectedMandiIds = $windowRows
            ->pluck('mandi')
            ->filter()
            ->unique()
            ->values()
            ->all();

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
            'lastSnapshotDate' => $this->snapshotDateFromWindowRows($windowRows),
            'crop_year' => $cropYear,
            'data' => $paddyMandi,
        ]);
    }

    public function getPaddyPrices(Request $request, $mandi_id, $state_id)
    {
        [$cropYear, $hasRows] = $this->resolvePaddyCropYearForWindow(
            $state_id,
            $mandi_id,
            $this->requestCropYear($request)
        );

        // Per quality: prefer today's price, else yesterday; skip if neither.
        // Each row keeps its own created_at (price date) for the client.
        $paddyPrices = collect();
        if ($hasRows) {
            $windowRows = $this->scopePaddyTodayOrYesterday(
                $this->paddyPriceBaseQuery($state_id, $mandi_id, $cropYear)
            )
                ->with(['getMandi_rel:id,mandi', 'getState_rel:id,state', 'quality_rel:id,type,quality'])
                ->orderByDesc('id')
                ->get();

            $paddyPrices = $this->preferTodayElseYesterdayPerKey($windowRows, 'quality_id');
        }

        $marketStatus = PaddyTradeCurrentStatus::current();
        $priceDefaults = PaddyPriceDefault::current()->toDefaultsArray();

        return response()->json([
            'status' => true,
            'message' => 'Paddy get successfully',
            'data' => $paddyPrices,
            'crop_year' => $cropYear,
            'lastUpdatedDate' => $this->lastUpdatedFromRows($paddyPrices),
            'lastSnapshotDate' => $this->snapshotDateFromWindowRows($paddyPrices),
            'defaults' => $priceDefaults,
            'currentStatus' => (int) $marketStatus->currentStatus,
            'statusMessage' => $marketStatus->message,
            'market_status' => [
                'currentStatus' => (int) $marketStatus->currentStatus,
                'label' => $marketStatus->status_label,
                'message' => $marketStatus->message,
            ],
        ]);
    }

    public function getPaddyPricesByPaddy(Request $request, $stateId, $paddyId)
    {
        $mandiId = $request->input('mandi_id', $request->input('mandi'));
        [$cropYear, $hasRows] = $this->resolvePaddyCropYearForWindow(
            $stateId,
            $mandiId,
            $this->requestCropYear($request)
        );

        // Per mandi for this crop: prefer today, else yesterday; skip if neither.
        $paddyPrices = collect();
        if ($hasRows) {
            $windowRows = $this->scopePaddyTodayOrYesterday(
                $this->paddyPriceBaseQuery($stateId, $mandiId, $cropYear)
                    ->where('quality_id', $paddyId)
            )
                ->with(['getMandi_rel:id,mandi', 'getState_rel:id,state', 'quality_rel:id,type,quality'])
                ->orderByDesc('id')
                ->get();

            $paddyPrices = $this->preferTodayElseYesterdayPerKey($windowRows, 'mandi');
        }

        $marketStatus = PaddyTradeCurrentStatus::current();

        return response()->json([
            'status' => true,
            'message' => 'Paddy get successfully',
            'data' => $paddyPrices,
            'crop_year' => $cropYear,
            'lastUpdatedDate' => $this->lastUpdatedFromRows($paddyPrices),
            'lastSnapshotDate' => $this->snapshotDateFromWindowRows($paddyPrices),
            'defaults' => PaddyPriceDefault::current()->toDefaultsArray(),
            'currentStatus' => (int) $marketStatus->currentStatus,
            'statusMessage' => $marketStatus->message,
            'market_status' => [
                'currentStatus' => (int) $marketStatus->currentStatus,
                'label' => $marketStatus->status_label,
                'message' => $marketStatus->message,
            ],
        ]);
    }

    public function getPaddyQualities(Request $request, $stateId)
    {
        $mandiId = $request->input('mandi_id', $request->input('mandi'));
        [$cropYear, $hasRows] = $this->resolvePaddyCropYearForWindow(
            $stateId,
            $mandiId,
            $this->requestCropYear($request)
        );

        $qualities = collect();
        $windowRows = collect();

        if ($hasRows) {
            $windowRows = $this->scopePaddyTodayOrYesterday(
                $this->paddyPriceBaseQuery($stateId, $mandiId, $cropYear)
            )->get(['id', 'quality_id', 'created_at', 'updated_at']);

            // Prefer today else yesterday per quality so a crop only listed once.
            $preferred = $this->preferTodayElseYesterdayPerKey($windowRows, 'quality_id');
            $paddyQualityArrayIds = $preferred
                ->pluck('quality_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($paddyQualityArrayIds !== []) {
                $qualities = PaddyQuality::query()
                    ->select('id', 'type', 'quality')
                    ->where('status', 1)
                    ->whereIn('id', $paddyQualityArrayIds)
                    ->orderByRaw('`order` IS NULL, `order` ASC')
                    ->orderBy('id')
                    ->get()
                    ->map(function ($row) {
                        return [
                            'id' => $row->id,
                            'name' => $row->quality,
                            'type' => $row->type,
                            'type_label' => $row->type_label,
                            'quality' => $row->quality,
                        ];
                    });
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Paddy mandi get successfully',
            'lastSnapshotDate' => $this->snapshotDateFromWindowRows($windowRows),
            'crop_year' => $cropYear,
            'data' => $qualities,
        ]);
    }

    public function GetPaddyMapData(Request $request, $mandi_id, $state_id, $quality_id)
    {
        $cropYear = $this->requestCropYear($request);
        if ($cropYear === null) {
            $cropYear = $this->latestPaddyCropYear($state_id, $mandi_id, $quality_id);
        }

        $rows = $this->paddyPriceBaseQuery($state_id, $mandi_id, $cropYear)
            ->where('quality_id', $quality_id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'min_hand_cutting_price', 'hand_cutting_price', 'min_machine_cutting_price', 'machine_cutting_price', 'created_at']);

        // Collapse to latest row per IST calendar day (same as live graph),
        // keeping date (Y-m-d) + amount (int). Graph uses MAX cutting prices;
        // falls back to min when max is empty (legacy / partial rows).
        $handByDay = [];
        $machineByDay = [];
        foreach ($rows as $row) {
            if (! $row->created_at) {
                continue;
            }
            $day = $row->created_at->copy()->timezone('Asia/Kolkata')->format('Y-m-d');
            $hand = $this->parsePaddyGraphAmount($row->hand_cutting_price);
            if ($hand === null) {
                $hand = $this->parsePaddyGraphAmount($row->min_hand_cutting_price);
            }
            if ($hand !== null) {
                $handByDay[$day] = $hand; // later rows overwrite => latest per day wins
            }
            $machine = $this->parsePaddyGraphAmount($row->machine_cutting_price);
            if ($machine === null) {
                $machine = $this->parsePaddyGraphAmount($row->min_machine_cutting_price);
            }
            if ($machine !== null) {
                $machineByDay[$day] = $machine;
            }
        }
        ksort($handByDay);
        ksort($machineByDay);

        // Union timeline across both cuttings.
        $allDays = array_values(array_unique(array_merge(array_keys($handByDay), array_keys($machineByDay))));
        sort($allDays);

        return response()->json([
            'status' => true,
            'crop_year' => $cropYear,
            'date' => $allDays,
            'handCutting' => $this->buildPaddyCuttingBlock($handByDay),
            'machineCutting' => $this->buildPaddyCuttingBlock($machineByDay),
        ]);
    }

    /**
     * Latest crop year that has prices for this state + mandi + quality.
     */
    private function latestPaddyCropYear($stateId, $mandiId, $qualityId): ?string
    {
        $year = $this->paddyPriceBaseQuery($stateId, $mandiId, null)
            ->where('quality_id', (int) $qualityId)
            ->whereNotNull('crop_year')
            ->where('crop_year', '!=', '')
            ->orderByDesc('crop_year')
            ->orderByDesc('id')
            ->value('crop_year');

        return $this->normalizedCropYear($year);
    }

    /**
     * Paddy price amount for graphs: stored as "min-max" range or "----".
     * Returns upper-bound int, or null when missing/invalid (same int
     * amount style as live graph max_price).
     */
    private function parsePaddyGraphAmount($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $str = trim((string) $value);
        if ($str === '' || $str === '----') {
            return null;
        }
        if (str_contains($str, '-')) {
            $parts = explode('-', $str);
            $str = trim(end($parts));
        }
        if (! is_numeric($str) || (float) $str <= 0) {
            return null;
        }

        return (int) $str;
    }

    /**
     * Build one cutting block: lowvalue/highvalue/lowdate/highdate plus
     * date + prices as objects (1-based index => value, so JSON encodes
     * objects) and the longest constant-price run.
     */
    private function buildPaddyCuttingBlock(array $byDay): array
    {
        $values = array_values($byDay);
        $lowvalue = $values !== [] ? min($values) : 0;
        $highvalue = $values !== [] ? max($values) : 0;
        $lowdate = '';
        $highdate = '';
        if ($values !== []) {
            $lowDay = array_search($lowvalue, $byDay);
            $highDay = array_search($highvalue, $byDay);
            $lowdate = $lowDay ? Carbon::parse($lowDay)->format('d-m-Y') : '';
            $highdate = $highDay ? Carbon::parse($highDay)->format('d-m-Y') : '';
        }

        // Longest constant-price run (same logic as live graph constantValue).
        $constantPrice = 0;
        $currVal = null;
        $currLen = 0;
        $bestVal = null;
        $bestLen = 0;
        foreach ($values as $val) {
            $num = (float) $val;
            if ($currVal === null || $num != $currVal) {
                $currVal = $num;
                $currLen = 1;
            } else {
                $currLen++;
            }
            if ($currLen > $bestLen) {
                $bestLen = $currLen;
                $bestVal = $num;
            }
        }
        if ($bestVal !== null) {
            $constantPrice = $bestVal;
        }

        // Plain sequential arrays => JSON arrays (values only, no keys).
        $date = [];
        $prices = [];
        foreach ($byDay as $day => $val) {
            $date[] = $day;
            $prices[] = (int) $val;
        }

        return [
            'lowvalue' => $lowvalue,
            'highvalue' => $highvalue,
            'lowdate' => $lowdate,
            'highdate' => $highdate,
            'date' => $date,
            'prices' => $prices,
            'constantPrice' => $constantPrice,
        ];
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
     * - packing (optional free text; packing_id is no longer sent from frontend)
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

        // Frontend sends free-text packing only (no packing_id)
        $packingText = $request->filled('packing')
            ? trim((string) $request->input('packing'))
            : null;
        if ($packingText === '') {
            $packingText = null;
        }

        $row = PaddySellQuery::create([
            'category' => $request->category,
            'quality' => (int) $request->quality,
            'quality_name' => $qualityName,
            'hand_combined' => $request->hand_combined,
            'packing_id' => null,
            'packing' => $packingText,
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

            QueuedMail::send('mail.PaddySellQueryReceived', $mailPayload, $mailTo, $subject, $mailFrom, $mailFromName);
        } catch (\Throwable $e) {
            // Do not fail the API if mail fails
            Log::error('Paddy sell query enquiry mail failed: ' . $e->getMessage(), [
                'paddy_sell_query_id' => $row->id ?? null,
            ]);
        }
    }

    /**
     * Distinct crop years for filters.
     *
     * Optional query:
     * - state|state_id — scopes years to paddy prices for that state
     *   (linked with list/web/paddy/state?cropYear=... / list/paddy/state?cropYear=...)
     * - mandi|mandi_id — further scopes to a mandi
     *
     * Without state/mandi: union of price + trade crop years.
     * With state/mandi: price crop years only for that scope.
     */
    public function listPaddyTradeCropYears(Request $request)
    {
        $stateId = $this->requestStateId($request);
        $mandiRaw = $request->input('mandi', $request->input('mandi_id'));
        $mandiId = ($mandiRaw !== null && $mandiRaw !== '' && is_numeric($mandiRaw))
            ? (int) $mandiRaw
            : null;

        $hasLocationFilter = $stateId !== null || $mandiId !== null;

        $fromPrices = $this->paddyPriceBaseQuery($stateId, $mandiId, null)
            ->whereNotNull('crop_year')
            ->where('crop_year', '!=', '')
            ->distinct()
            ->pluck('crop_year');

        $cropYears = $fromPrices;

        // Unfiltered list also includes trade crop years for trade screens.
        if (! $hasLocationFilter) {
            $fromTrades = PaddyTrade::query()
                ->whereNotNull('crop_year')
                ->where('crop_year', '!=', '')
                ->distinct()
                ->pluck('crop_year');

            $cropYears = $fromTrades->merge($fromPrices);
        }

        $cropYears = $cropYears
            ->map(fn ($year) => trim((string) $year))
            ->filter(fn ($year) => $year !== '')
            ->unique()
            ->sort(function ($a, $b) {
                return ((int) $b) <=> ((int) $a);
            })
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Paddy crop years list',
            'state' => $stateId,
            'mandi' => $mandiId,
            'data' => $cropYears,
        ], 200);
    }

    /**
     * List paddy trades for app & web portal (paginated).
     *
     * Default statuses (UI shows badge per trade):
     * - 1 Active, 4 In-Process, 12 Hold, 3 Sold
     * Deactivated (5) and Expired (2) are excluded from default list; use status=5|2 or status=all.
     * Past valid_days trades are auto-marked Expired (2) before listing.
     *
     * Optional query params:
     * - category: basmati | non-basmati
     * - quality: paddy_qualities id
     * - packing_id: seller packing id
     * - crop_year: exact crop year string on the trade
     * - hotdeal: 0 | 1
     * - userId | user_id: logged-in user (for per-trade is_interested / already_interested)
     * - seller_user_id: filter trades by original seller user id
     * - status: single status code, or comma list (e.g. 1,4,12,3), or "all"
     * - page: default 1
     * - per_page | perPage | limit: default 15, max 100
     */
    public function listPaddyTrades(Request $request)
    {
        PaddyTrade::expirePastValidDayTrades();

        $validator = Validator::make($request->all(), [
            'category' => 'nullable|in:basmati,non-basmati',
            'quality' => 'nullable|integer|exists:paddy_qualities,id',
            'packing_id' => 'nullable|integer|exists:sellerPackingINR,id',
            'crop_year' => 'nullable|string|max:50',
            'hotdeal' => 'nullable|in:0,1',
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
        if ($request->filled('crop_year')) {
            $query->where('crop_year', trim((string) $request->crop_year));
        }
        if ($request->has('hotdeal') && $request->input('hotdeal') !== '' && $request->input('hotdeal') !== null) {
            $query->where('hotdeal', (int) $request->hotdeal === 1 ? 1 : 0);
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
        PaddyTrade::expirePastValidDayTrades();

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

        PaddyTrade::expirePastValidDayTrades();

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
            $message = match ((int) $trade->status) {
                5 => 'This paddy trade is deactivated and not available for interest',
                2 => 'This paddy trade has expired and is not available for interest',
                default => 'This paddy trade is not available for interest (status: ' . $statusLabel . ')',
            };

            return response()->json([
                'status' => false,
                'message' => $message,
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

            QueuedMail::send('mail.PaddyTradeInterest', $mailPayload, $mailTo, $subject, $mailFrom, $mailFromName);
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
        $effectiveIsNew = $row->resolveEffectiveIsNew();

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
            'additional_information' => $row->additional_information,
            'lot_number' => $row->lot_number,
            'crop_year' => $row->crop_year,
            'status' => $row->status,
            'status_label' => $row->status_label,
            'is_new' => $effectiveIsNew ? 1 : 0,
            'is_new_label' => $effectiveIsNew ? 'yes' : 'no',
            'hotdeal' => (int) $row->hotdeal === 1 ? 1 : 0,
            'hotdeal_label' => (int) $row->hotdeal === 1 ? 'yes' : 'no',
            'valid_datetime_for_is_new' => $row->valid_datetime_for_is_new
                ? optional($row->valid_datetime_for_is_new)->format('Y-m-d H:i:s')
                : null,
            'sold_at_amount' => $row->sold_at_amount,
            'sold_at' => $row->sold_at,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ];
    }

    /**
     * Aggregated paddy price graph for the last N days (default 60).
     *
     * GET /api/paddy-prices/graph
     *
     * Query:
     * - qualityId|quality_id|seed (required): paddy_qualities id
     * - stateId|state_id|state (optional)
     * - cropYear|crop_year (optional)
     * - days (optional, default 60, max 365)
     *
     * Chart series: daily average handCutting / machineCutting across mandis.
     * Tooltip: per-day mandis[] with mandiName, handCutting, machineCutting + aggregates.
     */
    public function getPaddyPricesGraph(Request $request)
    {
        $qualityId = $request->input('qualityId', $request->input('quality_id', $request->input('seed')));
        $validator = Validator::make(
            array_merge($request->all(), ['qualityId' => $qualityId]),
            [
                'qualityId' => 'required|integer|min:1|exists:paddy_qualities,id',
                'days' => 'nullable|integer|min:1|max:365',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Please select a valid paddy quality / seed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $qualityId = (int) $qualityId;
        $days = (int) ($request->input('days', 60));
        if ($days < 1) {
            $days = 60;
        }

        $stateId = $this->requestStateId($request);
        $cropYear = $this->requestCropYear($request);

        $tz = config('app.timezone', 'Asia/Kolkata');
        $to = Carbon::now($tz)->startOfDay();
        $from = $to->copy()->subDays($days - 1);

        $quality = PaddyQuality::query()->select(['id', 'type', 'quality'])->find($qualityId);

        $rows = $this->paddyPriceBaseQuery($stateId, null, $cropYear)
            ->where('quality_id', $qualityId)
            ->where('created_at', '>=', $from->copy()->startOfDay())
            ->where('created_at', '<=', $to->copy()->endOfDay())
            ->with(['getMandi_rel:id,mandi'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get([
                'id',
                'mandi',
                'quality_id',
                'crop_year',
                'min_hand_cutting_price',
                'hand_cutting_price',
                'min_machine_cutting_price',
                'machine_cutting_price',
                'created_at',
            ]);

        // Latest row per IST day × mandi (same collapse rule as map/export graphs).
        $latestByDayMandi = [];
        foreach ($rows as $row) {
            if (! $row->created_at) {
                continue;
            }
            $day = $row->created_at->copy()->timezone($tz)->format('Y-m-d');
            $mandiId = (int) ($row->mandi ?? 0);
            if ($mandiId < 1) {
                continue;
            }
            $latestByDayMandi[$day][$mandiId] = $row;
        }
        ksort($latestByDayMandi);

        $dates = [];
        $handSeries = [];
        $machineSeries = [];
        $data = [];

        foreach ($latestByDayMandi as $day => $byMandi) {
            $mandis = [];
            $handValues = [];
            $machineValues = [];

            foreach ($byMandi as $mandiId => $row) {
                $hand = $this->parsePaddyGraphAmount($row->hand_cutting_price);
                if ($hand === null) {
                    $hand = $this->parsePaddyGraphAmount($row->min_hand_cutting_price);
                }
                $machine = $this->parsePaddyGraphAmount($row->machine_cutting_price);
                if ($machine === null) {
                    $machine = $this->parsePaddyGraphAmount($row->min_machine_cutting_price);
                }

                if ($hand === null && $machine === null) {
                    continue;
                }

                if ($hand !== null) {
                    $handValues[] = $hand;
                }
                if ($machine !== null) {
                    $machineValues[] = $machine;
                }

                $mandis[] = [
                    'mandiId' => (int) $mandiId,
                    'mandiName' => (string) (optional($row->getMandi_rel)->mandi ?? ''),
                    'handCutting' => $hand,
                    'machineCutting' => $machine,
                ];
            }

            if ($mandis === []) {
                continue;
            }

            usort($mandis, fn ($a, $b) => strcmp((string) $a['mandiName'], (string) $b['mandiName']));

            $handAvg = $handValues !== [] ? (int) round(array_sum($handValues) / count($handValues)) : null;
            $machineAvg = $machineValues !== [] ? (int) round(array_sum($machineValues) / count($machineValues)) : null;

            $dates[] = $day;
            $handSeries[] = $handAvg;
            $machineSeries[] = $machineAvg;

            $data[] = [
                'date' => $day,
                'handCutting' => $handAvg,
                'machineCutting' => $machineAvg,
                'mandiCount' => count($mandis),
                'mandis' => $mandis,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Paddy graph data fetched successfully.',
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'days' => $days,
            'qualityId' => $qualityId,
            'quality' => $quality?->quality,
            'type' => $quality ? PaddyPriceSeedFilter::canonicalType($quality->type) : null,
            'stateId' => $stateId,
            'crop_year' => $cropYear,
            'dates' => $dates,
            'handCutting' => $handSeries,
            'machineCutting' => $machineSeries,
            'data' => $data,
        ], 200);
    }

    /**
     * Earliest / latest calendar dates with paddy price entries (for historical from/to pickers).
     * GET /api/paddy-prices/historical-min-date
     */
    public function getPaddyPricesHistoricalMinDate()
    {
        $tz = config('app.timezone', 'Asia/Kolkata');
        $bounds = PaddyPrice::query()
            ->whereNotNull('created_at')
            ->selectRaw('MIN(created_at) as min_at, MAX(created_at) as max_at')
            ->first();

        $minDate = null;
        $maxDate = null;
        if ($bounds && $bounds->min_at) {
            $minDate = Carbon::parse($bounds->min_at)->timezone($tz)->format('Y-m-d');
        }
        if ($bounds && $bounds->max_at) {
            $maxDate = Carbon::parse($bounds->max_at)->timezone($tz)->format('Y-m-d');
        }

        return response()->json([
            'status' => true,
            'minDate' => $minDate,
            'maxDate' => $maxDate,
            'message' => $minDate
                ? 'Historical date range fetched successfully.'
                : 'No paddy price records found.',
        ], 200);
    }

    /**
     * Seed (quality) filters for paddy price export.
     *
     * GET /api/paddy-prices/filter-seeds
     * Optional ?type=basmati|non-basmati
     * Optional ?dataFor=today|historical
     *   - today → only seeds with price entries for today
     *   - historical → require from & to (Y-m-d); only seeds with entries in that range
     *
     * Each item includes `key` (e.g. 1121) for POST /api/paddy-prices/export `seedKey`.
     * When type is All, send seedKey — not a single quality id — because the same
     * seed name can exist on both basmati and non-basmati.
     */
    public function getPaddyPricesFilterSeeds(Request $request)
    {
        $type = strtolower(trim((string) $request->query('type', '')));
        if ($type === 'all') {
            $type = '';
        }
        if ($type !== '' && ! in_array($type, ['basmati', 'non-basmati'], true)) {
            return response()->json([
                'status' => false,
                'message' => 'Please select a valid type: Basmati or Non-Basmati.',
            ], 422);
        }

        $dataFor = strtolower(trim((string) $request->query('dataFor', '')));
        if ($dataFor === '') {
            $dataFor = null;
        }
        if ($dataFor !== null && ! in_array($dataFor, ['today', 'historical'], true)) {
            return response()->json([
                'status' => false,
                'message' => 'Please select a valid dataFor: today or historical.',
            ], 422);
        }

        $from = $request->query('from');
        $to = $request->query('to');
        $from = is_string($from) && trim($from) !== '' ? trim($from) : null;
        $to = is_string($to) && trim($to) !== '' ? trim($to) : null;

        if ($dataFor === 'historical') {
            $dateValidator = Validator::make(
                ['from' => $from, 'to' => $to],
                [
                    'from' => 'required|date_format:Y-m-d',
                    'to' => 'required|date_format:Y-m-d',
                ]
            );
            if ($dateValidator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'From and to dates (Y-m-d) are required for historical seed filters.',
                    'errors' => $dateValidator->errors(),
                ], 422);
            }
        }

        if ($dataFor === 'today') {
            [$from, $to] = $this->resolvedPaddyExportDates('today', null, null);
        } elseif ($dataFor === 'historical') {
            [$from, $to] = $this->resolvedPaddyExportDates('historical', $from, $to);
        } else {
            // No dataFor → keep previous behaviour (all active seeds).
            $from = null;
            $to = null;
        }

        $meta = [
            'status' => true,
            'dataFor' => $dataFor,
            'from' => $from,
            'to' => $to,
        ];

        if ($type === 'basmati' || $type === 'non-basmati') {
            return response()->json(array_merge($meta, [
                'type' => $type,
                'data' => PaddyPriceSeedFilter::listSeeds($type, $from, $to),
            ]), 200);
        }

        return response()->json(array_merge($meta, [
            'data' => [
                'basmati' => PaddyPriceSeedFilter::listSeeds('basmati', $from, $to),
                'non-basmati' => PaddyPriceSeedFilter::listSeeds('non-basmati', $from, $to),
            ],
        ]), 200);
    }

    /**
     * Paddy prices XLSX export.
     * POST /api/paddy-prices/export
     *
     * downloadType=mail    → queue email to the authenticated user's email
     * downloadType=browser → generate file and return a download URL
     *
     * Filters:
     * - range: today | historical
     * - type: null/omit = All, or basmati|non-basmati
     * - seedKey: null/omit = All seeds for the type scope, or a quality name (1121, 1509)
     * - seed: legacy single paddy_qualities id (used only when seedKey is omitted)
     * - from/to: required when range=historical (Y-m-d)
     */
    public function exportPaddyPrices(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Authentication is required. Please sign in and try again.',
                'session_expired' => true,
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'downloadType' => 'required|in:mail,browser',
            'range' => 'required|in:today,historical',
            'type' => 'nullable|in:basmati,non-basmati,all',
            'from' => 'nullable|required_if:range,historical|date_format:Y-m-d',
            'to' => 'nullable|required_if:range,historical|date_format:Y-m-d',
            'seedKey' => 'nullable|string|max:191',
            'seed' => 'nullable|integer|min:1|exists:paddy_qualities,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Please review the selected options and try again.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $downloadType = strtolower(trim((string) $request->input('downloadType')));
        $range = strtolower(trim((string) $request->input('range')));

        $type = $request->input('type');
        $type = is_string($type) && $type !== '' ? strtolower(trim($type)) : null;
        if ($type === 'all') {
            $type = null;
        }

        $seedKey = $request->input('seedKey');
        $seedKey = is_string($seedKey) && trim($seedKey) !== '' ? trim($seedKey) : null;

        $legacySeedId = $request->input('seed');
        $legacySeedId = is_numeric($legacySeedId) && (int) $legacySeedId > 0
            ? (int) $legacySeedId
            : null;

        if ($seedKey !== null || $legacySeedId === null) {
            $qualityIds = PaddyPriceSeedFilter::resolveQualityIds($type, $seedKey, null);
        } else {
            $qualityIds = PaddyPriceSeedFilter::resolveQualityIds($type, null, $legacySeedId);
        }

        [$from, $to] = $this->resolvedPaddyExportDates($range, $request->input('from'), $request->input('to'));

        $export = new PaddyPricesApiExport($from, $to, $qualityIds);
        $rowCount = (clone $export->query())->count();
        if ($rowCount === 0) {
            return response()->json([
                'status' => false,
                'downloadType' => $downloadType,
                'message' => 'No records match the selected criteria. Please adjust your filters and try again.',
                'download_url' => null,
                'range' => $range,
                'from' => $from,
                'to' => $to,
                'type' => $type,
                'seedKey' => $seedKey,
                'qualityIds' => $qualityIds,
            ], 404);
        }

        if ($downloadType === 'mail') {
            $email = $this->resolvedExportEmail($user);
            if ($email === null) {
                return response()->json([
                    'status' => false,
                    'message' => 'A valid email address is required to send this export. Please update your profile and try again.',
                ], 422);
            }

            ExportPaddyPricesMailJob::dispatch(
                $email,
                $from,
                $to,
                $qualityIds,
                $type,
                $seedKey,
                $range
            );

            return response()->json([
                'status' => true,
                'downloadType' => 'mail',
                'message' => 'Your export has been queued. You will receive an email shortly.',
                'email' => $email,
                'range' => $range,
                'from' => $from,
                'to' => $to,
                'type' => $type,
                'seedKey' => $seedKey,
                'qualityIds' => $qualityIds,
            ], 202);
        }

        @ini_set('memory_limit', '512M');

        $seedSlug = $seedKey
            ? '_'.Str::slug($seedKey)
            : ($qualityIds ? '_seed-'.implode('-', $qualityIds) : '');

        $filename = 'SNTC_paddy_prices_'.Str::slug(($from ?? 'start').'_'.($to ?? 'end'))
            .$seedSlug
            .($type ? '_'.Str::slug($type) : '')
            .'_'.now()->format('YmdHis').'.xlsx';

        Excel::store($export, $filename, 'publicexcel');

        return response()->json([
            'status' => true,
            'downloadType' => 'browser',
            'message' => 'Your export is ready for download.',
            'download_url' => url('excel/'.$filename),
            'filename' => $filename,
            'rowCount' => $rowCount,
            'range' => $range,
            'from' => $from,
            'to' => $to,
            'type' => $type,
            'seedKey' => $seedKey,
            'qualityIds' => $qualityIds,
        ], 200);
    }

    /**
     * @return array{0:?string,1:?string}
     */
    private function resolvedPaddyExportDates(string $range, mixed $from, mixed $to): array
    {
        $tz = config('app.timezone', 'Asia/Kolkata');

        if ($range === 'today') {
            $today = Carbon::now($tz)->format('Y-m-d');

            return [$today, $today];
        }

        $from = is_string($from) && $from !== '' ? $from : null;
        $to = is_string($to) && $to !== '' ? $to : null;
        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    private function resolvedExportEmail($user): ?string
    {
        $email = trim((string) ($user->email ?? ''));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $personalEmail = trim((string) optional($user->getWebPersonalDetails)->email);
            if ($personalEmail !== '' && filter_var($personalEmail, FILTER_VALIDATE_EMAIL)) {
                $email = $personalEmail;
            }
        }

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $email;
    }
}

