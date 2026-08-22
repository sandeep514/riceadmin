<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebRiceBagProductImage extends Model
{
    protected $table = 'web_rice_bag_product_images';

    protected $fillable = [
        'product_id',
        'file_name',
        'sort_order',
    ];

    public function product()
    {
        return $this->belongsTo(WebRiceBagProduct::class, 'product_id', 'id');
    }
}
