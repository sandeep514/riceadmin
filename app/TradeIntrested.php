<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TradeIntrested extends Model
{
    protected $fillable = ['tradeId','userId','status'];
    protected $table = 'trade_intrested';

    // public function name_rel(){
    //     return $this->belongsTo(RiceName::class,'name','id')->orderBy('order','ASC');
    // }

    // public function form_rel(){
    //     return $this->belongsTo(RiceForm::class,'form','id')->where('status' , 1);
    // }
}
