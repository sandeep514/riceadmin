<?php

namespace App\Http\Controllers;

use App\PaddyQuality;
use App\PaddySellQuery;
use App\PaddyTrade;
use App\PaddyTradeCurrentStatus;
use App\SellerPackingINR;
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

        $qualities = PaddyQuality::query()
            ->where('status', 1)
            ->orderByRaw('`order` IS NULL, `order` ASC')
            ->orderBy('quality')
            ->get(['id', 'quality', 'type']);

        $packings = SellerPackingINR::query()
            ->where('status', 1)
            ->orderBy('packing')
            ->get(['id', 'packing']);

        $categoryOptions = PaddyQuality::riceTypeOptions();
        $handCombinedOptions = ['Hand' => 'Hand', 'Combined' => 'Combined'];

        return view('paddySellQuery.convert', compact(
            'query',
            'qualities',
            'packings',
            'categoryOptions',
            'handCombinedOptions'
        ));
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

        $validator = Validator::make($request->all(), [
            'category' => 'required|in:basmati,non-basmati',
            'quality' => 'required|integer|exists:paddy_qualities,id',
            'hand_combined' => 'required|string|max:100',
            'packing_id' => 'nullable|integer|exists:sellerPackingINR,id',
            'contact_number' => 'required|string|max:50',
            'contact_person' => 'required|string|max:255',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'location' => 'required|string|max:255',
            'quantity' => 'required|string|max:100',
            'rate' => 'required|string|max:100',
            'valid_days' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $imageName = $query->image;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $uploadDir = public_path('uploads');
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $imageName = 'paddy_trade_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $imageName);
        }

        // Quality label comes from master (dropdown selection only)
        $qualityName = optional(PaddyQuality::find($request->quality))->quality;

        $packingId = $request->filled('packing_id') ? (int) $request->packing_id : null;
        $packingLabel = $packingId
            ? optional(SellerPackingINR::find($packingId))->packing
            : null;

        DB::transaction(function () use ($request, $query, $imageName, $qualityName, $packingId, $packingLabel) {
            PaddyTrade::create([
                'paddy_sell_query_id' => $query->id,
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
                'valid_days' => $request->valid_days,
                'type' => $request->input('type', $query->type ?: 'web'),
                'user_id' => $query->user_id,
                'remarks' => $request->input('remarks'),
                'status' => 1,
                'created_by' => Auth::id(),
            ]);

            $query->update(['status' => 2]);
        });

        Session::flash('success', 'Success|Paddy sell query converted to paddy trade successfully.');

        return redirect()->route('list.paddy.trades');
    }

    public function listTrades()
    {
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
     * Update individual paddy trade status.
     * status: 1 Active, 4 In-Process, 12 Hold, 3 Sold
     * sold_at_amount optional when status = Sold
     */
    public function updateTradeStatus(Request $request, $id)
    {
        $trade = PaddyTrade::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|integer|in:1,4,12,3',
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
