<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TradeCategoryMap extends Model
{
    protected $table = 'trade_category_map';

    protected $fillable = ['trade_id', 'category_id', 'status'];

    public function trade()
    {
        return $this->belongsTo(TradeQueriesINR::class, 'trade_id', 'id');
    }

    public function category_rel()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
