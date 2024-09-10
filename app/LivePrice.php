<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LivePrice extends Model
{
    protected $fillable = ['name','form','cropGrade','cropYear','min_price','max_price','state','up_down','state_order','status'];

    public function name_rel(){
        return $this->belongsTo(RiceName::class,'name','id')->orderBy('order','ASC');
    }

    public function form_rel(){
        return $this->belongsTo(RiceForm::class,'form','id')->where('status' , 1);
    }

    public function lastWeekRecord(){
        return $this->hasOne(LivePrice::class, 'id')
        ->where('name', $this->name)
        ->where('state', $this->state)
        ->where('cropGrade', $this->cropGrade);
        // ->whereDate('created_at', $this->created_at->subDays(7)->format('Y-m-d'));
    }

}