<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebRiceBagProduct extends Model
{
    protected $table = 'web_rice_bag_products';

    protected $fillable = [
        'user_id',
        'bag_type_id',
        'other_type_value',
        'specification',
        'description',
        'additional_information',
        'packing_form_id',
        'packing_form',
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
}
