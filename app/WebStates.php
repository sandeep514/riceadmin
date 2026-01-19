<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebStates extends Model
{
    protected $table = "web_states";
    protected $fillable = ['state_code' , 'state_name' ,'order_no'];
}