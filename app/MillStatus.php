<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MillStatus extends Model
{
    protected $fillable = ['date','seller','visit_status','remarks'];
}
