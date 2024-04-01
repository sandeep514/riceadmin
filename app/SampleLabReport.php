<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SampleLabReport extends Model
{
    protected $table = "sample_lab_report";
    protected  $fillable = ['order_id' , 'remarks' , 'status'];
}
