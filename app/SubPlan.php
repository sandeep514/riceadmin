<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubPlan extends Model
{
    protected $fillable = ['name'];
    protected $table = "sub_plan";
}