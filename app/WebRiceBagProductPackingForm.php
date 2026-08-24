<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebRiceBagProductPackingForm extends Model
{
    protected $table = 'web_rice_bag_product_packing_forms';

    protected $fillable = [
        'product_id',
        'packing_form_id',
        'packing_form',
    ];

    public function product()
    {
        return $this->belongsTo(WebRiceBagProduct::class, 'product_id', 'id');
    }
}
