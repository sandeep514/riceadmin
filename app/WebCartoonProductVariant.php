<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebCartoonProductVariant extends Model
{
    protected $table = 'web_cartoon_product_variants';

    protected $fillable = [
        'product_id',
        'packing_size_id',
        'packing_size',
        'rate',
        'gst',
        'total_price',
        'bag_size',
        'bag_weight',
        'image',
        'sort_order',
    ];

    public function product()
    {
        return $this->belongsTo(WebCartoonProduct::class, 'product_id', 'id');
    }
}
