<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Coupon extends Model
{
    protected $table = "coupon";
    protected $fillable = [
        'coupon_name',
        'coupon_feature',
        'coupon_description',
        'coupon_percentage',
        'coupon_expiry',
        'status',
        'maxDiscount'
    ];

}