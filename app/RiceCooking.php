<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiceCooking extends Model
{
    protected $fillable = [
        'sample_id',
        'sample_soak_qty',
        'soak_time_starts',
        'soak_time_end',
        'cooking_start_time',
        'cooking_end_time',
        'weight_before_soak',
        'weight_after_soak',
        'weight_after_cook',
        'status'
    ];

    protected $table = "rice_cooking";
}