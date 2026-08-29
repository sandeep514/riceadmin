<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebLabEquipmentProduct extends Model
{
    protected $table = 'web_lab_equipment_products';

    protected $fillable = [
        'user_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function variants()
    {
        return $this->hasMany(WebLabEquipmentProductVariant::class, 'product_id', 'id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
