<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebPlanKeysModel extends Model
{
    protected $table = 'web_plan_keys';
    protected $fillable = ['key' ,'status'];
}

