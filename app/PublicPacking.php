<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublicPacking extends Model
{
    protected $table = "public_packing_milestone3";
    protected $fillable = ['size' , 'packing' , 'order' ,'status'];
}