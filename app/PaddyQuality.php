<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaddyQuality extends Model
{
    protected $table = 'paddy_qualities';

    protected $fillable = [
        'quality',
        'description',
        'order',
        'status',
    ];
}
