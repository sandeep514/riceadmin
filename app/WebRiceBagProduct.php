<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebRiceBagProduct extends Model
{
    protected $table = 'web_rice_bag_products';

    protected $fillable = [
        'user_id',
        'bag_type_id',
        'specification',
        'description',
        'additional_information',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function packingForms()
    {
        return $this->hasMany(WebRiceBagProductPackingForm::class, 'product_id', 'id')
            ->orderBy('id');
    }

    public function packingSizes()
    {
        return $this->hasMany(WebRiceBagProductPackingSize::class, 'product_id', 'id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
