<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaddyQuality extends Model
{
    protected $table = 'paddy_qualities';

    protected $fillable = [
        'rice_type_id',
        'quality',
        'description',
        'order',
        'status',
    ];

    public function riceType()
    {
        return $this->belongsTo(RiceType::class, 'rice_type_id');
    }
}

