<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\SubPlan;

class Plan extends Model
{
    protected $fillable = ['plan_name' , 'sub_plan','chart_int','price'];
    protected $table = "plan";
    
    public function sub_plan(){
        return $this->belongsTo( 'App\SubPlan', 'sub_plan', 'id' );
    }
    
    public function ChartInterval(){
        return $this->belongsTo( 'App\ChartInterval' , 'chart_int' , 'id' );
    }
}