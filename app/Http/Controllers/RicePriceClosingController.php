<?php

namespace App\Http\Controllers;

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

        RicePriceClosing::create([
            'close_date' => $date,
            'reason' => trim((string) $request->input('reason')),
            'created_by' => auth()->id(),
        ]);

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

        $closing->update([
            'close_date' => $date,
            'reason' => trim((string) $request->input('reason')),
        ]);

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
        $closing->delete();

        Cache::increment('web:prices:version');

        Session::flash('success', 'Success|Closing deleted.');

        return redirect()->route('rice-price-closings.index', array_filter(['month' => $month]));
    }
}
