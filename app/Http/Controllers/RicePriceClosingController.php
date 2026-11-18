<?php

namespace App\Http\Controllers;

use App\AppSetting;
use App\LivePriceStatusMessage;
use App\PaddyTradeCurrentStatus;
use App\RicePriceClosing;
use App\TradeCurrentStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Session;

class RicePriceClosingController extends Controller
{
    public function index(Request $request)
    {
        $market = RicePriceClosing::normalizeMarket($request->query('market'));
        $month = $request->query('month'); // YYYY-MM optional filter
        if (! is_string($month) || ! preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = Carbon::now('Asia/Kolkata')->format('Y-m');
        }

        try {
            $monthStart = Carbon::createFromFormat('Y-m-d', $month.'-01', 'Asia/Kolkata')->startOfMonth();
        } catch (\Exception $e) {
            $monthStart = Carbon::now('Asia/Kolkata')->startOfMonth();
            $month = $monthStart->format('Y-m');
        }
        $monthEnd = $monthStart->copy()->endOfMonth();

        $query = RicePriceClosing::query()
            ->whereDate('close_date', '>=', $monthStart->toDateString())
            ->whereDate('close_date', '<=', $monthEnd->toDateString())
            ->orderBy('close_date');
        if (Schema::hasColumn('rice_price_closings', 'market')) {
            $query->where('market', $market);
        }
        $closings = $query->get();

        $upcoming = RicePriceClosing::upcoming(20, $market);
        $marketLabel = RicePriceClosing::marketLabel($market);

        return view('rice_price_closings.index', compact(
            'closings',
            'upcoming',
            'month',
            'monthStart',
            'market',
            'marketLabel'
        ));
    }

    public function store(Request $request)
    {
        $market = RicePriceClosing::normalizeMarket($request->input('market', $request->query('market')));

        $validator = Validator::make($request->all(), [
            'close_date' => 'required|date_format:Y-m-d',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $date = $request->input('close_date');
        $existsQuery = RicePriceClosing::query()->whereDate('close_date', $date);
        if (Schema::hasColumn('rice_price_closings', 'market')) {
            $existsQuery->where('market', $market);
        }
        if ($existsQuery->exists()) {
            Session::flash('error', 'Error|A closing is already set for '.$date.'.');

            return redirect()->back()->withInput();
        }

        $reason = trim((string) $request->input('reason'));
        $payload = [
            'close_date' => $date,
            'reason' => $reason,
            'created_by' => auth()->id(),
        ];
        if (Schema::hasColumn('rice_price_closings', 'market')) {
            $payload['market'] = $market;
        }
        RicePriceClosing::create($payload);

        $this->syncTodayClosingIfNeeded($market, $date, $reason);

        if ($market === RicePriceClosing::MARKET_LIVE) {
            Cache::increment('web:prices:version');
        }

        Session::flash('success', 'Success|Upcoming '.RicePriceClosing::marketLabel($market).' closing added.');

        return redirect()->route('rice-price-closings.index', [
            'market' => $market,
            'month' => Carbon::parse($date)->format('Y-m'),
        ]);
    }

    public function update(Request $request, $id)
    {
        $closing = RicePriceClosing::find($id);
        if (! $closing) {
            Session::flash('error', 'Error|Closing not found.');

            return back();
        }

        $market = Schema::hasColumn('rice_price_closings', 'market')
            ? RicePriceClosing::normalizeMarket((string) ($closing->market ?? RicePriceClosing::MARKET_LIVE))
            : RicePriceClosing::MARKET_LIVE;

        $validator = Validator::make($request->all(), [
            'close_date' => 'required|date_format:Y-m-d',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $date = $request->input('close_date');
        $duplicateQuery = RicePriceClosing::query()
            ->whereDate('close_date', $date)
            ->where('id', '!=', $closing->id);
        if (Schema::hasColumn('rice_price_closings', 'market')) {
            $duplicateQuery->where('market', $market);
        }
        if ($duplicateQuery->exists()) {
            Session::flash('error', 'Error|Another closing already exists for '.$date.'.');

            return redirect()->back()->withInput();
        }

        $reason = trim((string) $request->input('reason'));
        $closing->update([
            'close_date' => $date,
            'reason' => $reason,
        ]);

        $this->syncTodayClosingIfNeeded($market, $date, $reason);

        if ($market === RicePriceClosing::MARKET_LIVE) {
            Cache::increment('web:prices:version');
        }

        Session::flash('success', 'Success|Closing updated.');

        return redirect()->route('rice-price-closings.index', [
            'market' => $market,
            'month' => Carbon::parse($date)->format('Y-m'),
        ]);
    }

    public function destroy($id)
    {
        $closing = RicePriceClosing::find($id);
        if (! $closing) {
            Session::flash('error', 'Error|Closing not found.');

            return back();
        }

        $market = Schema::hasColumn('rice_price_closings', 'market')
            ? RicePriceClosing::normalizeMarket((string) ($closing->market ?? RicePriceClosing::MARKET_LIVE))
            : RicePriceClosing::MARKET_LIVE;
        $month = $closing->close_date ? $closing->close_date->format('Y-m') : null;
        $wasToday = $closing->close_date
            && $closing->close_date->toDateString() === Carbon::now('Asia/Kolkata')->toDateString();
        $closing->delete();

        // Removing today's special closing: drop leftover manual override so schedule applies.
        if ($wasToday) {
            $this->clearOverrideForMarket($market);
        }

        if ($market === RicePriceClosing::MARKET_LIVE) {
            Cache::increment('web:prices:version');
        }

        Session::flash('success', 'Success|Closing deleted.');

        return redirect()->route('rice-price-closings.index', array_filter([
            'market' => $market,
            'month' => $month,
        ]));
    }

    /**
     * If the closing is for today (IST), clear any manual Open override and sync status.
     */
    private function syncTodayClosingIfNeeded(string $market, string $date, string $reason): void
    {
        $today = Carbon::now('Asia/Kolkata')->toDateString();
        if ($date !== $today) {
            return;
        }

        $this->clearOverrideForMarket($market);

        if ($market === RicePriceClosing::MARKET_LIVE) {
            $message = $reason !== '' ? ('closed - '.$reason) : 'closed';
            LivePriceStatusMessage::query()->where('id', 1)->update([
                'currentStatus' => 2,
                'message' => $message,
            ]);

            return;
        }

        $message = $reason !== '' ? ('Market closed - '.$reason) : 'Market closed';
        if ($market === RicePriceClosing::MARKET_RICE_SOURCING && Schema::hasTable('trade_current_status')) {
            TradeCurrentStatus::query()->where('id', 1)->update([
                'currentStatus' => AppSetting::TRADE_STATUS_CLOSED,
                'message' => $message,
            ]);
        }
        if ($market === RicePriceClosing::MARKET_PADDY && Schema::hasTable('paddy_trade_current_status')) {
            PaddyTradeCurrentStatus::current()->update([
                'currentStatus' => AppSetting::TRADE_STATUS_CLOSED,
                'message' => $message,
            ]);
        }
    }

    private function clearOverrideForMarket(string $market): void
    {
        $settings = AppSetting::current();
        if ($market === RicePriceClosing::MARKET_LIVE) {
            $settings->clearManualMarketOverride();
        } elseif ($market === RicePriceClosing::MARKET_RICE_SOURCING) {
            $settings->clearManualRiceSourcingOverride();
        } elseif ($market === RicePriceClosing::MARKET_PADDY) {
            $settings->clearManualPaddyMarketOverride();
        }
    }
}
