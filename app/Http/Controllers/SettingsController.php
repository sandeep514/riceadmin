<?php

namespace App\Http\Controllers;

use App\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Session;

class SettingsController extends Controller
{
    private const TIME_RULE = ['required', 'regex:/^([01]?\d|2[0-3]):[0-5]\d$/'];

    public function index()
    {
        $settings = AppSetting::current();
        $resolved = $settings->resolvedLiveMarket();
        $resolvedRice = $settings->resolvedRiceSourcingMarket();
        $resolvedPaddy = $settings->resolvedPaddyMarket();
        $callNow = $settings->callNowPayload();

        return view('settings.index', compact(
            'settings',
            'resolved',
            'resolvedRice',
            'resolvedPaddy',
            'callNow'
        ));
    }

    public function update(Request $request)
    {
        $section = (string) $request->input('section', 'all');

        $sections = [
            'call_now' => [
                'fields' => ['call_now_from', 'call_now_to'],
                'messages' => [
                    'call_now_from.regex' => 'Call Now From must be HH:MM (24h IST).',
                    'call_now_to.regex' => 'Call Now To must be HH:MM (24h IST).',
                ],
                'label' => 'Call Now timing',
                'anchor' => 'call-now',
                'bust_prices' => false,
            ],
            'live_market' => [
                'fields' => ['market_from', 'market_to'],
                'messages' => [
                    'market_from.regex' => 'Market From must be HH:MM (24h IST).',
                    'market_to.regex' => 'Market To must be HH:MM (24h IST).',
                ],
                'label' => 'Market open / close timing',
                'anchor' => 'live-market',
                'bust_prices' => true,
            ],
            'rice_sourcing' => [
                'fields' => ['rice_sourcing_from', 'rice_sourcing_to'],
                'messages' => [
                    'rice_sourcing_from.regex' => 'Rice sourcing From must be HH:MM (24h IST).',
                    'rice_sourcing_to.regex' => 'Rice sourcing To must be HH:MM (24h IST).',
                ],
                'label' => 'Rice sourcing timing',
                'anchor' => 'rice-sourcing',
                'bust_prices' => false,
                'requires_columns' => ['rice_sourcing_from', 'rice_sourcing_to'],
            ],
            'paddy_market' => [
                'fields' => ['paddy_market_from', 'paddy_market_to'],
                'messages' => [
                    'paddy_market_from.regex' => 'Paddy market From must be HH:MM (24h IST).',
                    'paddy_market_to.regex' => 'Paddy market To must be HH:MM (24h IST).',
                ],
                'label' => 'Paddy pricing timing',
                'anchor' => 'paddy-market',
                'bust_prices' => false,
                'requires_columns' => ['paddy_market_from', 'paddy_market_to'],
            ],
        ];

        if (! isset($sections[$section])) {
            Session::flash('error', 'Error|Unknown settings section.');

            return redirect()->route('settings.index');
        }

        $config = $sections[$section];

        if (! empty($config['requires_columns'])) {
            foreach ($config['requires_columns'] as $col) {
                if (! Schema::hasColumn('app_settings', $col)) {
                    Session::flash('error', 'Error|Please run the latest migration before saving '.$config['label'].'.');

                    return redirect()->route('settings.index')->withFragment($config['anchor']);
                }
            }
        }

        $rules = [];
        foreach ($config['fields'] as $field) {
            $rules[$field] = self::TIME_RULE;
        }

        $validator = Validator::make($request->all(), $rules, $config['messages']);
        if ($validator->fails()) {
            return redirect()
                ->route('settings.index')
                ->withFragment($config['anchor'])
                ->withErrors($validator)
                ->withInput();
        }

        $payload = [];
        foreach ($config['fields'] as $field) {
            $payload[$field] = $this->padTime((string) $request->input($field));
        }

        AppSetting::current()->update($payload);

        if (! empty($config['bust_prices'])) {
            Cache::increment('web:prices:version');
        }

        Session::flash('success', 'Success|'.$config['label'].' saved successfully.');

        return redirect()->route('settings.index')->withFragment($config['anchor']);
    }

    private function padTime(string $value): string
    {
        if (preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', trim($value), $m)) {
            return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        }

        return trim($value);
    }
}
