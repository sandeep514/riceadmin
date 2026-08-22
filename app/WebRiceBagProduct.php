<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebRiceBagProduct extends Model
{
    protected $table = 'web_rice_bag_products';

    protected $fillable = [
        'user_id',
        'product_name',
        'rice_name_id',
        'rice_form_id',
        'rice_form',
        'bag_color',
        'print_type',
        'description',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function packingSizes()
    {
        return $this->hasMany(WebRiceBagProductPackingSize::class, 'product_id', 'id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function images()
    {
        return $this->hasMany(WebRiceBagProductImage::class, 'product_id', 'id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function riceName()
    {
        return $this->belongsTo(RiceName::class, 'rice_name_id', 'id');
    }
}
