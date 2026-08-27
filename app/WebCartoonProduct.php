<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebCartoonProduct extends Model
{
    protected $table = 'web_cartoon_products';

    protected $fillable = [
        'user_id',
        'cartoon_type_id',
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
        return $this->hasMany(WebCartoonProductVariant::class, 'product_id', 'id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
