<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CookingReport extends Model
{
    protected $fillable = ['sntc_no','remarks'];

    public static $status = [
        'average' => 'Average',
        'good' => 'Good',
        'very_good' => 'Very Good',
        'excellent' => 'Excellent'
    ];
}
