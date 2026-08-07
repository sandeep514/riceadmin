<?php

namespace App\Http\Controllers;

use App\PaddyQuality;
use App\PaddySellQuery;
use App\PaddyTrade;
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
        $queries = PaddySellQuery::with(['paddyQuality', 'user'])
            ->orderByDesc('id')
            ->get();

        return view('paddySellQuery.index', compact('queries'));
    }

    public function view($id)
    {
        $query = PaddySellQuery::with(['paddyQuality', 'user', 'paddyTrade'])->findOrFail($id);

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

        $categoryOptions = PaddyQuality::riceTypeOptions();
        $handCombinedOptions = ['Hand' => 'Hand', 'Combined' => 'Combined'];

        return view('paddySellQuery.convert', compact(
            'query',
            'qualities',
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
            'quality_name' => 'nullable|string|max:255',
            'hand_combined' => 'required|string|max:100',
            'packing' => 'nullable|string|max:255',
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

        $qualityName = $request->input('quality_name');
        if (! $qualityName) {
            $qualityName = optional(PaddyQuality::find($request->quality))->quality;
        }

        DB::transaction(function () use ($request, $query, $imageName, $qualityName) {
            PaddyTrade::create([
                'paddy_sell_query_id' => $query->id,
                'category' => $request->category,
                'quality' => (int) $request->quality,
                'quality_name' => $qualityName,
                'hand_combined' => $request->hand_combined,
                'packing' => $request->input('packing'),
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
        $trades = PaddyTrade::with(['paddyQuality', 'user', 'paddySellQuery'])
            ->orderByDesc('id')
            ->get();

        return view('paddySellQuery.trades', compact('trades'));
    }

    public function viewTrade($id)
    {
        $trade = PaddyTrade::with(['paddyQuality', 'user', 'paddySellQuery', 'creator'])->findOrFail($id);

        return view('paddySellQuery.trade_view', compact('trade'));
    }

    public function closeTrade($id)
    {
        $trade = PaddyTrade::findOrFail($id);
        $trade->update(['status' => 0]);

        Session::flash('success', 'Success|Paddy trade closed successfully.');

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
