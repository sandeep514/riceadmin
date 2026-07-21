<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaddyStateModel extends Model
{
    protected $table = "paddyStates";
    protected $fillable = ['state', 'status', 'order_no'];
}