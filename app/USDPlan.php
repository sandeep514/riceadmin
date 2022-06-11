<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class USDPlan extends Model
{
    protected $table = "USDPlan";
    protected $fillable = [
        'plan_name',
        'plan_desc',
        'valid_months',
        'actual_price',
        'discounted_prie',
        'status'
    ];

}