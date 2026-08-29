<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebMachineryEquipmentProductVariant extends Model
{
    protected $table = 'web_machinery_equipment_product_variants';

    protected $fillable = [
        'product_id',
        'equipment_id',
        'equipment_name',
        'rate',
        'description',
        'catalogue',
        'sort_order',
    ];

    public function product()
    {
        return $this->belongsTo(WebMachineryEquipmentProduct::class, 'product_id', 'id');
    }
}
