<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrialPeriod extends Model
{
    protected $table = 'trialPeriod'; 
    protected $fillable = [
        'month'
    ];
}