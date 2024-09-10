<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SampleLabReportParameters extends Model
{
    protected $table = "sample_lab_report_parameters";
    protected  $fillable = ['sample_lab_reports_id','parameter_id','value','status'];
}
