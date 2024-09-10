<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SellerPackingINR extends Model
{
    protected $table = "sellerPackingINR";
    protected $fillable = ['packing' , 'description', 'status'];
}