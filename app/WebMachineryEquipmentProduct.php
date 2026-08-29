<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebMachineryEquipmentProduct extends Model
{
    protected $table = 'web_machinery_equipment_products';

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
        return $this->hasMany(WebMachineryEquipmentProductVariant::class, 'product_id', 'id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
