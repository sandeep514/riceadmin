<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LivePrice extends Model
{
    protected $fillable = ['name','form','min_price','max_price','state','up_down','state_order','status'];

    public function name_rel(){
        return $this->belongsTo(RiceName::class,'name','id')->orderBy('order','ASC');
    }

    public function form_rel(){
        return $this->belongsTo(RiceForm::class,'form','id')->where('status' , 1);
    }
}
