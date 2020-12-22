<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LivePrice extends Model
{
    protected $fillable = ['name','form','min_price','max_price','state','up_down'];

    public function name_rel(){
        return $this->belongsTo(RiceName::class,'name','id');
    }

    public function form_rel(){
        return $this->belongsTo(RiceForm::class,'form','id');
    }
}
