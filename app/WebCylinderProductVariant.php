<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebCylinderProductVariant extends Model
{
    protected $table = 'web_cylinder_product_variants';

    protected $fillable = [
        'product_id',
        'packing_size_id',
        'packing_size',
        'other_size_value',
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
        return $this->belongsTo(WebCylinderProduct::class, 'product_id', 'id');
    }
}
