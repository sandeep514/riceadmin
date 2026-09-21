<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebForwarderCharge extends Model
{
    protected $table = 'web_forwarder_charges';

    public const CURRENCY_INR = 'INR';

    public const CURRENCY_USD = 'USD';

    protected $fillable = [
        'product_id',
        'title_id',
        'title',
        'currency',
        'currency_id',
        'charges',
        'exchange_rate',
        'inr_amount',
        'remarks',
        'is_other',
        'sort_order',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'title_id' => 'integer',
        'currency_id' => 'integer',
        'is_other' => 'integer',
        'sort_order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(WebForwarderProduct::class, 'product_id', 'id');
    }

    public function titleRel()
    {
        return $this->belongsTo(VendorForwarderChargeTitle::class, 'title_id', 'id');
    }

    public function currencyRel()
    {
        return $this->belongsTo(VendorCurrency::class, 'currency_id', 'id');
    }
}
