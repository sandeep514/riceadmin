<?php

namespace App\Http\Controllers;

use App\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Session;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = AppSetting::current();
        $resolved = $settings->resolvedLiveMarket();
        $callNow = $settings->callNowPayload();

        return view('settings.index', compact('settings', 'resolved', 'callNow'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'call_now_from' => ['required', 'regex:/^([01]?\d|2[0-3]):[0-5]\d$/'],
            'call_now_to' => ['required', 'regex:/^([01]?\d|2[0-3]):[0-5]\d$/'],
            'market_from' => ['required', 'regex:/^([01]?\d|2[0-3]):[0-5]\d$/'],
            'market_to' => ['required', 'regex:/^([01]?\d|2[0-3]):[0-5]\d$/'],
        ], [
            'call_now_from.regex' => 'Call Now From must be HH:MM (24h IST).',
            'call_now_to.regex' => 'Call Now To must be HH:MM (24h IST).',
            'market_from.regex' => 'Market From must be HH:MM (24h IST).',
            'market_to.regex' => 'Market To must be HH:MM (24h IST).',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $settings = AppSetting::current();
        $settings->update([
            'call_now_from' => $this->padTime($request->input('call_now_from')),
            'call_now_to' => $this->padTime($request->input('call_now_to')),
            'market_from' => $this->padTime($request->input('market_from')),
            'market_to' => $this->padTime($request->input('market_to')),
        ]);

        Cache::increment('web:prices:version');

        Session::flash('success', 'Success|Settings saved successfully.');

        return redirect()->route('settings.index');
    }

    private function padTime(string $value): string
    {
        if (preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', trim($value), $m)) {
            return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        }

        return trim($value);
    }
}
