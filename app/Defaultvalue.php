<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Defaultvalue extends Model
{
    protected $fillable = ['localcharges','financecost','dollarvalue','bagcost'];
    protected $table = 'default_value';
}
