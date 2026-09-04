<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebLabEquipmentProductVariant extends Model
{
    protected $table = 'web_lab_equipment_product_variants';

    protected $fillable = [
        'product_id',
        'equipment_id',
        'equipment_name',
        'rate',
        'description',
        'image',
        'catalogue',
        'sort_order',
    ];

    public function product()
    {
        return $this->belongsTo(WebLabEquipmentProduct::class, 'product_id', 'id');
    }
}
