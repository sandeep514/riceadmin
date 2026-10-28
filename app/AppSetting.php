<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = [
        'call_now_from',
        'call_now_to',
        'market_from',
        'market_to',
        'market_override_status',
        'market_override_until',
    ];

    protected $casts = [
        'market_override_until' => 'datetime',
    ];

    public static function current(): self
    {
        $row = self::query()->orderBy('id')->first();
        if ($row) {
            return $row;
        }

        $attrs = [
            'call_now_from' => '09:00',
            'call_now_to' => '18:00',
            'market_from' => '09:00',
            'market_to' => '18:00',
        ];
        if (Schema::hasColumn('app_settings', 'market_override_status')) {
            $attrs['market_override_status'] = null;
            $attrs['market_override_until'] = null;
        }

        return self::create($attrs);
    }

    public function isCallNowAvailable(?Carbon $at = null): bool
    {
        return $this->isWithinWindow(
            (string) $this->call_now_from,
            (string) $this->call_now_to,
            $at
        );
    }

    public function isMarketOpenBySchedule(?Carbon $at = null): bool
    {
        return $this->isWithinWindow(
            (string) $this->market_from,
            (string) $this->market_to,
            $at
        );
    }

    /**
     * True when now (IST) is inside [from, to]. Supports overnight windows (e.g. 22:00–06:00).
     */
    public function isWithinWindow(string $from, string $to, ?Carbon $at = null): bool
    {
        $from = $this->normalizeTime($from);
        $to = $this->normalizeTime($to);
        if ($from === null || $to === null) {
            return false;
        }

        $now = ($at ? $at->copy() : Carbon::now('Asia/Kolkata'))
            ->timezone('Asia/Kolkata')
            ->format('H:i');

        if ($from <= $to) {
            return $now >= $from && $now <= $to;
        }

        return $now >= $from || $now <= $to;
    }

    public function callNowPayload(?Carbon $at = null): array
    {
        return [
            'from' => $this->normalizeTime((string) $this->call_now_from) ?? '09:00',
            'to' => $this->normalizeTime((string) $this->call_now_to) ?? '18:00',
            'timezone' => 'Asia/Kolkata',
            'is_available' => $this->isCallNowAvailable($at),
        ];
    }

    /**
     * Always automatic from schedule, unless a live/price manual override is still active.
     *
     * @return array{currentStatus:int,message:string,mode:string,schedule:array,override_until:?string}
     */
    public function resolvedLiveMarket(?Carbon $at = null): array
    {
        $now = $at ? $at->copy()->timezone('Asia/Kolkata') : Carbon::now('Asia/Kolkata');

        $schedule = [
            'from' => $this->normalizeTime((string) $this->market_from) ?? '09:00',
            'to' => $this->normalizeTime((string) $this->market_to) ?? '18:00',
            'timezone' => 'Asia/Kolkata',
            'in_window' => $this->isMarketOpenBySchedule($now),
        ];

        $this->clearExpiredOverride($now);

        $overrideStatus = $this->market_override_status ?? null;
        $overrideUntil = $this->market_override_until ?? null;
        if ($overrideStatus !== null && $overrideUntil && $now->lt($overrideUntil)) {
            $code = (int) $overrideStatus;
            $message = $code === 1 ? 'open' : ($code === 3 ? 'hold' : 'closed');

            return [
                'currentStatus' => $code,
                'message' => $message,
                'mode' => 'manual_override',
                'schedule' => $schedule,
                'override_until' => $overrideUntil->timezone('Asia/Kolkata')->format('Y-m-d H:i'),
            ];
        }

        $open = $schedule['in_window'];

        return [
            'currentStatus' => $open ? 1 : 2,
            'message' => $open ? 'open' : 'closed',
            'mode' => 'auto',
            'schedule' => $schedule,
            'override_until' => null,
        ];
    }

    public function livePriceStatusMessagePayload(?Carbon $at = null): array
    {
        $resolved = $this->resolvedLiveMarket($at);

        return [
            'id' => 1,
            'currentStatus' => $resolved['currentStatus'],
            'message' => $resolved['message'],
            'mode' => $resolved['mode'],
            'schedule' => $resolved['schedule'],
            'override_until' => $resolved['override_until'],
        ];
    }

    /**
     * Admin Open / Closed / Hold from live/price — temporary override until next schedule boundary.
     */
    public function applyManualMarketOverride(int $statusCode, string $message): void
    {
        $until = $this->nextScheduleBoundary();

        $payload = [];
        if (Schema::hasColumn('app_settings', 'market_override_status')) {
            $payload['market_override_status'] = $statusCode;
            $payload['market_override_until'] = $until;
        }
        // Legacy column if present — keep unused; schedule is always the default.
        if (Schema::hasColumn('app_settings', 'market_mode')) {
            $payload['market_mode'] = 'auto';
        }
        if ($payload !== []) {
            $this->update($payload);
        }

        LivePriceStatusMessage::query()->where('id', 1)->update([
            'currentStatus' => $statusCode,
            'message' => $message,
        ]);
    }

    /**
     * Next IST moment when the market schedule flips (open→close or close→open).
     */
    public function nextScheduleBoundary(?Carbon $at = null): Carbon
    {
        $now = $at ? $at->copy()->timezone('Asia/Kolkata') : Carbon::now('Asia/Kolkata');
        $from = $this->normalizeTime((string) $this->market_from) ?? '09:00';
        $to = $this->normalizeTime((string) $this->market_to) ?? '18:00';

        [$fromH, $fromM] = array_map('intval', explode(':', $from));
        [$toH, $toM] = array_map('intval', explode(':', $to));

        $fromToday = $now->copy()->setTime($fromH, $fromM, 0);
        $toToday = $now->copy()->setTime($toH, $toM, 0);

        if ($from <= $to) {
            // Same-day window: next flip is toToday if still before it, else fromTomorrow.
            if ($now->lt($toToday)) {
                return $toToday;
            }

            return $fromToday->copy()->addDay();
        }

        // Overnight window: open from→midnight and midnight→to.
        if ($now->gte($fromToday)) {
            return $toToday->copy()->addDay();
        }
        if ($now->lte($toToday)) {
            return $toToday;
        }

        return $fromToday;
    }

    private function clearExpiredOverride(Carbon $now): void
    {
        if (! Schema::hasColumn('app_settings', 'market_override_status')) {
            return;
        }
        if ($this->market_override_until && $now->gte($this->market_override_until)) {
            $this->forceFill([
                'market_override_status' => null,
                'market_override_until' => null,
            ])->save();
        }
    }

    private function normalizeTime(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        if (preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', $value, $m)) {
            return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        }

        try {
            return Carbon::parse($value, 'Asia/Kolkata')->format('H:i');
        } catch (\Exception $e) {
            return null;
        }
    }
}
