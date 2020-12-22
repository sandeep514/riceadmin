<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChartInterval extends Model
{
    protected $fillable = ['name'];
    protected $table = "chartinterval";
}