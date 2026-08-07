<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaddyTradeInterested extends Model
{
    protected $table = 'paddy_trade_interested';

    protected $fillable = [
        'paddy_trade_id',
        'user_id',
        'status',
    ];

    public function paddyTrade()
    {
        return $this->belongsTo(PaddyTrade::class, 'paddy_trade_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
