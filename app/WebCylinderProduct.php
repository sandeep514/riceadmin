<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebCylinderProduct extends Model
{
    protected $table = 'web_cylinder_products';

    protected $fillable = [
        'user_id',
        'cylinder_type_id',
        'specification',
        'description',
        'additional_information',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function variants()
    {
        return $this->hasMany(WebCylinderProductVariant::class, 'product_id', 'id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
