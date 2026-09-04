<?php

namespace App\Http\Controllers;

use App\PaddyQuality;
use App\PaddySellQuery;
use App\PaddyTrade;
use App\PaddyTradeCurrentStatus;
use App\SellerPackingINR;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Session;

class PaddySellQueryController extends Controller
{
    public function index()
    {
        $queries = PaddySellQuery::with(['paddyQuality', 'user', 'packingRel'])
            ->orderByDesc('id')
            ->get();

        return view('paddySellQuery.index', compact('queries'));
    }

    public function view($id)
    {
        $query = PaddySellQuery::with(['paddyQuality', 'user', 'paddyTrade', 'packingRel'])->findOrFail($id);

        return view('paddySellQuery.view', compact('query'));
    }

    public function downloadImage($id)
    {
        $query = PaddySellQuery::findOrFail($id);

        if (! $query->image) {
            Session::flash('error', 'Error|No image found for this query.');

            return back();
        }

        $path = public_path('uploads/' . ltrim($query->image, '/'));

        if (! is_file($path)) {
            Session::flash('error', 'Error|Image file is missing on server.');

            return back();
        }

        return response()->download($path, basename($path));
    }

    /**
     * Admin create paddy trade form (no sell query required).
     */
    public function createTrade()
    {
        $formData = $this->paddyTradeFormData();

        return view('paddySellQuery.create_trade', $formData);
    }

    /**
     * Admin save new paddy trade.
     */
    public function saveTrade(Request $request)
    {
        $validator = Validator::make($request->all(), $this->paddyTradeValidationRules(true));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $payload = $this->buildPaddyTradePayload($request, [
            'paddy_sell_query_id' => null,
            'user_id' => $request->filled('user_id') ? (int) $request->user_id : null,
            'type' => $request->input('type', 'admin'),
            'image_fallback' => null,
        ]);

        $trade = PaddyTrade::create($payload);

        Session::flash('success', 'Success|Paddy trade created successfully.');

        return redirect()->route('view.paddy.trade', $trade->id);
    }

    /**
     * Admin edit paddy trade form.
     */
    public function editTrade($id)
    {
        $trade = PaddyTrade::with(['paddyQuality', 'user', 'packingRel'])->findOrFail($id);
        $formData = $this->paddyTradeFormData();

        return view('paddySellQuery.edit_trade', array_merge($formData, [
            'trade' => $trade,
        ]));
    }

    /**
     * Admin update paddy trade.
     */
    public function updateTrade(Request $request, $id)
    {
        $trade = PaddyTrade::findOrFail($id);

        $validator = Validator::make($request->all(), $this->paddyTradeValidationRules(true));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $payload = $this->buildPaddyTradePayload($request, [
            'paddy_sell_query_id' => $trade->paddy_sell_query_id,
            'user_id' => $request->filled('user_id') ? (int) $request->user_id : null,
            'type' => $request->input('type', $trade->type ?: 'admin'),
            'image_fallback' => $trade->image,
        ]);

        // Do not overwrite admin status / sold meta on field edit
        unset($payload['status'], $payload['created_by'], $payload['sold_at_amount'], $payload['sold_at']);

        $trade->update($payload);

        Session::flash('success', 'Success|Paddy trade updated successfully.');

        return redirect()->route('view.paddy.trade', $trade->id);
    }

    /**
     * Convert to paddy trade — prefilled form page.
     */
    public function convertToTrade($id)
    {
        $query = PaddySellQuery::with(['paddyQuality', 'user'])->findOrFail($id);

        if ((int) $query->status === 0) {
            Session::flash('error', 'Error|Cannot convert a closed paddy sell query.');

            return redirect()->route('list.paddy.sell.queries');
        }

        if ((int) $query->status === 2 || PaddyTrade::where('paddy_sell_query_id', $query->id)->exists()) {
            Session::flash('error', 'Error|This paddy sell query is already converted to trade.');

            return redirect()->route('list.paddy.sell.queries');
        }

        $formData = $this->paddyTradeFormData();

        return view('paddySellQuery.convert', array_merge($formData, [
            'query' => $query,
        ]));
    }

    /**
     * Save paddy trade from sell query convert form.
     */
    public function saveConvertToTrade(Request $request, $id)
    {
        $query = PaddySellQuery::findOrFail($id);

        if ((int) $query->status === 0) {
            Session::flash('error', 'Error|Cannot convert a closed paddy sell query.');

            return redirect()->route('list.paddy.sell.queries');
        }

        if ((int) $query->status === 2 || PaddyTrade::where('paddy_sell_query_id', $query->id)->exists()) {
            Session::flash('error', 'Error|This paddy sell query is already converted to trade.');

            return redirect()->route('list.paddy.sell.queries');
        }

        $validator = Validator::make($request->all(), $this->paddyTradeValidationRules(false));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $payload = $this->buildPaddyTradePayload($request, [
            'paddy_sell_query_id' => $query->id,
            'user_id' => $query->user_id,
            'type' => $request->input('type', $query->type ?: 'web'),
            'image_fallback' => $query->image,
        ]);

        DB::transaction(function () use ($query, $payload) {
            PaddyTrade::create($payload);
            $query->update(['status' => 2]);
        });

        Session::flash('success', 'Success|Paddy sell query converted to paddy trade successfully.');

        return redirect()->route('list.paddy.trades');
    }

    /**
     * Shared dropdown data for create/convert paddy trade forms.
     */
    private function paddyTradeFormData(): array
    {
        $qualities = PaddyQuality::query()
            ->where('status', 1)
            ->orderByRaw('`order` IS NULL, `order` ASC')
            ->orderBy('quality')
            ->get(['id', 'quality', 'type']);

        $packings = SellerPackingINR::query()
            ->where('status', 1)
            ->orderBy('packing')
            ->get(['id', 'packing']);

        $users = User::query()
            ->orderByDesc('id')
            ->limit(500)
            ->get(['id', 'name', 'email', 'phone', 'mobile', 'companyname']);

        return [
            'qualities' => $qualities,
            'packings' => $packings,
            'users' => $users,
            'categoryOptions' => PaddyQuality::riceTypeOptions(),
            'handCombinedOptions' => ['Hand' => 'Hand', 'Combined' => 'Combined'],
        ];
    }

    private function paddyTradeValidationRules(bool $adminCreate): array
    {
        $rules = [
            'category' => 'required|in:basmati,non-basmati',
            'quality' => 'required|integer|exists:paddy_qualities,id',
            'hand_combined' => 'required|string|max:100',
            'packing' => 'nullable|string|max:255',
            'packing_id' => 'nullable|integer|exists:sellerPackingINR,id',
            'contact_number' => 'required|string|max:50',
            'contact_person' => 'required|string|max:255',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'location' => 'required|string|max:255',
            'quantity' => 'required|string|max:100',
            'rate' => 'required|string|max:100',
            'valid_days' => 'required|date',
            'type' => 'nullable|string|max:50',
            'remarks' => 'nullable|string',
            'additional_information' => 'nullable|string',
            'lot_number' => 'nullable|string|max:100',
            'crop_year' => 'nullable|string|max:50',
            'is_new' => 'nullable|in:0,1',
            'valid_datetime_for_is_new' => 'nullable|date',
        ];

        if ($adminCreate) {
            $rules['user_id'] = 'nullable|integer|exists:users,id';
        }

        return $rules;
    }

    /**
     * @param  array{paddy_sell_query_id:?int,user_id:?int,type:string,image_fallback:?string}  $options
     */
    private function buildPaddyTradePayload(Request $request, array $options): array
    {
        $imageName = $options['image_fallback'] ?? null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $uploadDir = public_path('uploads');
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $imageName = 'paddy_trade_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $imageName);
        }

        $qualityName = optional(PaddyQuality::find($request->quality))->quality;

        // Prefer free-text packing; optional packing_id still resolves label if provided
        $packingId = $request->filled('packing_id') ? (int) $request->packing_id : null;
        $packingLabel = null;
        if ($request->filled('packing')) {
            $packingLabel = trim((string) $request->input('packing'));
            if ($packingLabel === '') {
                $packingLabel = null;
            }
        } elseif ($packingId) {
            $packingLabel = optional(SellerPackingINR::find($packingId))->packing;
        }

        return [
            'paddy_sell_query_id' => $options['paddy_sell_query_id'] ?? null,
            'category' => $request->category,
            'quality' => (int) $request->quality,
            'quality_name' => $qualityName,
            'hand_combined' => $request->hand_combined,
            'packing_id' => $packingId,
            'packing' => $packingLabel,
            'contact_number' => $request->contact_number,
            'contact_person' => $request->contact_person,
            'image' => $imageName,
            'location' => $request->location,
            'quantity' => $request->quantity,
            'rate' => $request->rate,
            'valid_days' => $this->normalizeValidDays($request->input('valid_days')),
            'type' => $options['type'] ?? $request->input('type', 'admin'),
            'user_id' => $options['user_id'] ?? null,
            'remarks' => $request->input('remarks'),
            'additional_information' => $request->input('additional_information'),
            'lot_number' => $request->filled('lot_number') ? trim((string) $request->input('lot_number')) : null,
            'crop_year' => $request->filled('crop_year') ? trim((string) $request->input('crop_year')) : null,
            'status' => 1,
            'is_new' => (int) $request->input('is_new', 0) === 1 ? 1 : 0,
            'valid_datetime_for_is_new' => $this->normalizeValidDatetimeForIsNew($request),
            'created_by' => Auth::id(),
        ];
    }

    private function normalizeValidDays($raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw)
                ->timezone(config('app.timezone', 'Asia/Kolkata'))
                ->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return is_string($raw) ? trim($raw) : null;
        }
    }

    private function normalizeValidDatetimeForIsNew(Request $request): ?string
    {
        if ((int) $request->input('is_new', 0) !== 1) {
            return null;
        }

        $raw = $request->input('valid_datetime_for_is_new');
        if ($raw === null || $raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw)
                ->timezone(config('app.timezone', 'Asia/Kolkata'))
                ->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function listTrades()
    {
        PaddyTrade::expirePastValidDayTrades();

        $trades = PaddyTrade::with(['paddyQuality', 'user', 'paddySellQuery', 'packingRel'])
            ->orderByDesc('id')
            ->get();

        $marketStatus = PaddyTradeCurrentStatus::current();
        $marketStatusLabels = PaddyTradeCurrentStatus::$marketStatus;
        $currentMarketStatus = (int) $marketStatus->currentStatus;
        $currentMarketLabel = $marketStatusLabels[$currentMarketStatus] ?? $marketStatus->message;

        return view('paddySellQuery.trades', compact(
            'trades',
            'marketStatus',
            'currentMarketStatus',
            'currentMarketLabel'
        ));
    }

    /**
     * Update paddy market status: 1 open, 11 closed, 12 hold.
     */
    public function updateMarketStatus($tradeStatus)
    {
        $tradeStatus = (int) $tradeStatus;
        $allowed = PaddyTradeCurrentStatus::$marketStatusMessages;

        if (! array_key_exists($tradeStatus, $allowed)) {
            Session::flash('error', 'Error|Invalid market status.');

            return back();
        }

        $row = PaddyTradeCurrentStatus::current();
        $row->update([
            'currentStatus' => $tradeStatus,
            'message' => $allowed[$tradeStatus],
        ]);

        Session::flash('success', 'Success|Paddy market status updated to ' . ($allowed[$tradeStatus] ?? $tradeStatus) . '.');

        return back();
    }

    public function viewTrade($id)
    {
        PaddyTrade::expirePastValidDayTrades();

        $trade = PaddyTrade::with(['paddyQuality', 'user', 'paddySellQuery', 'creator', 'packingRel'])->findOrFail($id);

        return view('paddySellQuery.trade_view', compact('trade'));
    }

    public function closeTrade($id)
    {
        // Legacy close route — map to Hold. Prefer updateTradeStatus for full status set.
        $trade = PaddyTrade::findOrFail($id);
        $trade->update(['status' => 12]);

        Session::flash('success', 'Success|Paddy trade set to Hold.');

        return back();
    }

    /**
     * Toggle is_new Yes/No on a paddy trade.
     */
    public function updateTradeIsNew(Request $request, $id)
    {
        $trade = PaddyTrade::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'is_new' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $isNew = (int) $request->is_new === 1 ? 1 : 0;
        $payload = ['is_new' => $isNew];
        if ($isNew === 0) {
            $payload['valid_datetime_for_is_new'] = null;
        }
        $trade->update($payload);

        Session::flash('success', 'Success|Paddy trade Is New set to ' . ($isNew ? 'Yes' : 'No') . '.');

        return back();
    }

    /**
     * Update individual paddy trade status.
     * status: 1 Active, 4 In-Process, 12 Hold, 3 Sold
     * sold_at_amount optional when status = Sold
     */
    public function updateTradeStatus(Request $request, $id)
    {
        $trade = PaddyTrade::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|integer|in:1,4,12,3,5',
            'sold_at_amount' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $status = (int) $request->status;
        $payload = ['status' => $status];

        if ($status === 3) {
            // Sold — amount optional (empty string clears it)
            $payload['sold_at_amount'] = $request->filled('sold_at_amount')
                ? $request->sold_at_amount
                : null;
            $payload['sold_at'] = now();
        } else {
            // Leaving sold state clears sold meta
            $payload['sold_at_amount'] = null;
            $payload['sold_at'] = null;
        }

        $trade->update($payload);

        $label = PaddyTrade::$statusLabels[$status] ?? $status;
        Session::flash('success', 'Success|Paddy trade status updated to ' . $label . '.');

        return back();
    }

    public function close($id)
    {
        $query = PaddySellQuery::findOrFail($id);
        $query->update(['status' => 0]);

        Session::flash('success', 'Success|Paddy sell query closed successfully.');

        return back();
    }
}
