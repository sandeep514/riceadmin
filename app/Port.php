<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    protected $fillable = ['banner' , 'state','route','price','state_order'];
}
