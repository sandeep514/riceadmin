<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebRiceBagProductPackingSize extends Model
{
    protected $table = 'web_rice_bag_product_packing_sizes';

    protected $fillable = [
        'product_id',
        'packing_id',
        'packing',
        'packing_form',
        'available_quantity',
        'price',
        'sort_order',
    ];

    public function product()
    {
        return $this->belongsTo(WebRiceBagProduct::class, 'product_id', 'id');
    }
}
