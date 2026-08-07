<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaddyTradeCurrentStatus extends Model
{
    protected $table = 'paddy_trade_current_status';

    protected $fillable = [
        'currentStatus',
        'message',
    ];

    /**
     * Market status map (same codes as rice trade market).
     * 1 = open, 11 = closed, 12 = hold
     */
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

    public function getStatusLabelAttribute(): string
    {
        return self::$marketStatus[(int) $this->currentStatus] ?? (string) $this->currentStatus;
    }
}
