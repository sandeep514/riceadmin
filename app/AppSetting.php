<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = [
        'call_now_from',
        'call_now_to',
        'market_from',
        'market_to',
        'market_mode',
    ];

    public static function current(): self
    {
        $row = self::query()->orderBy('id')->first();
        if ($row) {
            return $row;
        }

        return self::create([
            'call_now_from' => '09:00',
            'call_now_to' => '18:00',
            'market_from' => '09:00',
            'market_to' => '18:00',
            'market_mode' => 'manual',
        ]);
    }

    public function isAutoMarket(): bool
    {
        return strtolower(trim((string) $this->market_mode)) === 'auto';
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

        // Overnight: e.g. 22:00 → 06:00
        return $now >= $from || $now <= $to;
    }

    /**
     * Payload for APIs / admin about Call Now.
     */
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
     * Resolved live market status for API (respects auto schedule vs manual override).
     *
     * @return array{currentStatus:int,message:string,mode:string,schedule:array}
     */
    public function resolvedLiveMarket(?Carbon $at = null): array
    {
        $schedule = [
            'from' => $this->normalizeTime((string) $this->market_from) ?? '09:00',
            'to' => $this->normalizeTime((string) $this->market_to) ?? '18:00',
            'timezone' => 'Asia/Kolkata',
            'in_window' => $this->isMarketOpenBySchedule($at),
        ];

        if ($this->isAutoMarket()) {
            $open = $schedule['in_window'];

            return [
                'currentStatus' => $open ? 1 : 2,
                'message' => $open ? 'open' : 'closed',
                'mode' => 'auto',
                'schedule' => $schedule,
            ];
        }

        $row = LivePriceStatusMessage::query()->orderBy('id')->first();
        $code = (int) ($row->currentStatus ?? 1);
        $message = (string) ($row->message ?? 'open');
        if ($message === '') {
            $message = $code === 1 ? 'open' : ($code === 3 ? 'hold' : 'closed');
        }

        return [
            'currentStatus' => $code,
            'message' => $message,
            'mode' => 'manual',
            'schedule' => $schedule,
        ];
    }

    /**
     * Object-shaped payload matching historical livePriceStatusMessage + extras.
     */
    public function livePriceStatusMessagePayload(?Carbon $at = null): array
    {
        $resolved = $this->resolvedLiveMarket($at);

        return [
            'id' => 1,
            'currentStatus' => $resolved['currentStatus'],
            'message' => $resolved['message'],
            'mode' => $resolved['mode'],
            'schedule' => $resolved['schedule'],
        ];
    }

    public function enableAutoMarket(): void
    {
        $this->update(['market_mode' => 'auto']);
    }

    public function enableManualMarket(): void
    {
        $this->update(['market_mode' => 'manual']);
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
