<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Buyerpackinginr extends Model
{
    protected $table = "buyer_packing_INR";
    protected $fillable = ['packing' , 'description', 'status'];
}