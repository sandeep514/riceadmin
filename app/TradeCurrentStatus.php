<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TradeCurrentStatus extends Model
{
    protected $table = 'trade_current_status';

    protected $fillable = ['trade_id', 'currentStatus', 'message'];

    public static $marketStatus = [
        1 => 'open',
        11 => 'closed',
        12 => 'hold',
    ];

    public static $marketStatusMessages = [
        1 => 'open',
        11 => 'Market closed',
        12 => 'Market on hold',
    ];

    /**
     * DB row (legacy). Prefer resolved() for display / APIs.
     */
    public static function current(): self
    {
        $row = self::query()->orderBy('id')->first();
        if ($row) {
            return $row;
        }

        return self::create([
            'currentStatus' => 1,
            'message' => self::$marketStatusMessages[1],
        ]);
    }

    /**
     * Schedule + temporary override (IST), same pattern as live prices.
     *
     * @return array{currentStatus:int,message:string,label:string,mode:string,schedule:array,override_until:?string}
     */
    public static function resolved(?\Carbon\Carbon $at = null): array
    {
        return AppSetting::current()->resolvedRiceSourcingMarket($at);
    }

    /**
     * Overlay resolved status onto the legacy row for views that expect a model.
     */
    public static function currentResolved(?\Carbon\Carbon $at = null): self
    {
        $resolved = self::resolved($at);
        $row = self::current();
        $row->currentStatus = $resolved['currentStatus'];
        $row->message = $resolved['message'];
        $row->setAttribute('resolved_mode', $resolved['mode']);
        $row->setAttribute('resolved_schedule', $resolved['schedule']);
        $row->setAttribute('override_until', $resolved['override_until']);

        return $row;
    }

    /**
     * API-shaped market status (includes schedule open/close / override meta).
     * Same shape as PaddyTradeCurrentStatus::marketStatusApiPayload().
     */
    public static function marketStatusApiPayload(?\Carbon\Carbon $at = null): array
    {
        $resolved = self::resolved($at);

        return [
            'currentStatus' => $resolved['currentStatus'],
            'label' => $resolved['label'],
            'message' => $resolved['message'],
            'mode' => $resolved['mode'],
            'schedule' => $resolved['schedule'],
            'override_until' => $resolved['override_until'],
            'special_closing' => $resolved['special_closing'] ?? null,
            'closing_notice' => $resolved['closing_notice'] ?? null,
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::$marketStatus[(int) $this->currentStatus] ?? (string) $this->currentStatus;
    }
}
