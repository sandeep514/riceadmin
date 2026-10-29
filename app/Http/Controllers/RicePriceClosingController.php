<?php

namespace App\Http\Controllers;

use App\AppSetting;
use App\LivePriceStatusMessage;
use App\RicePriceClosing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Session;

class RicePriceClosingController extends Controller
{
    public function index(Request $request)
    {
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

        $closings = RicePriceClosing::query()
            ->whereDate('close_date', '>=', $monthStart->toDateString())
            ->whereDate('close_date', '<=', $monthEnd->toDateString())
            ->orderBy('close_date')
            ->get();

        $upcoming = RicePriceClosing::upcoming(20);

        return view('rice_price_closings.index', compact('closings', 'upcoming', 'month', 'monthStart'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'close_date' => 'required|date_format:Y-m-d',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $date = $request->input('close_date');
        $exists = RicePriceClosing::query()->whereDate('close_date', $date)->exists();
        if ($exists) {
            Session::flash('error', 'Error|A closing is already set for '.$date.'.');
            return redirect()->back()->withInput();
        }

        $reason = trim((string) $request->input('reason'));
        RicePriceClosing::create([
            'close_date' => $date,
            'reason' => $reason,
            'created_by' => auth()->id(),
        ]);

        $this->syncTodayClosingIfNeeded($date, $reason);

        Cache::increment('web:prices:version');

        Session::flash('success', 'Success|Upcoming rice price closing added.');

        return redirect()->route('rice-price-closings.index', [
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

        $validator = Validator::make($request->all(), [
            'close_date' => 'required|date_format:Y-m-d',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $date = $request->input('close_date');
        $duplicate = RicePriceClosing::query()
            ->whereDate('close_date', $date)
            ->where('id', '!=', $closing->id)
            ->exists();
        if ($duplicate) {
            Session::flash('error', 'Error|Another closing already exists for '.$date.'.');
            return redirect()->back()->withInput();
        }

        $reason = trim((string) $request->input('reason'));
        $closing->update([
            'close_date' => $date,
            'reason' => $reason,
        ]);

        $this->syncTodayClosingIfNeeded($date, $reason);

        Cache::increment('web:prices:version');

        Session::flash('success', 'Success|Closing updated.');

        return redirect()->route('rice-price-closings.index', [
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

        $month = $closing->close_date ? $closing->close_date->format('Y-m') : null;
        $wasToday = $closing->close_date
            && $closing->close_date->toDateString() === Carbon::now('Asia/Kolkata')->toDateString();
        $closing->delete();

        // Removing today's special closing: drop leftover manual override so schedule applies.
        if ($wasToday) {
            AppSetting::current()->clearManualMarketOverride();
        }

        Cache::increment('web:prices:version');

        Session::flash('success', 'Success|Closing deleted.');

        return redirect()->route('rice-price-closings.index', array_filter(['month' => $month]));
    }

    /**
     * If the closing is for today (IST), clear any manual Open override and sync status.
     */
    private function syncTodayClosingIfNeeded(string $date, string $reason): void
    {
        $today = Carbon::now('Asia/Kolkata')->toDateString();
        if ($date !== $today) {
            return;
        }

        AppSetting::current()->clearManualMarketOverride();

        $message = $reason !== '' ? ('closed - '.$reason) : 'closed';
        LivePriceStatusMessage::query()->where('id', 1)->update([
            'currentStatus' => 2,
            'message' => $message,
        ]);
    }
}
