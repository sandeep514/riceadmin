<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class USD_prices extends Model
{
    protected $table = 'USD_prices';
    protected $fillable = ['rice','ricemin','ricemax','transportmin','transportmax','category','charges','dollarrate','percentageValue','totalMin','totalMax','exchangeRatemin','exchangeRatemax','fobmin','fobmax','status','usd_defaultMaster_id','user_id','color_status'];

    protected $color_status = [ 1 => 'green' , '2' => 'red' ,3 => 'black' ];

    public function getRiceQuality()
    {
        return $this->belongsTo(QualityMaster::class,'rice','id');
    }
    public function getUSDDefaultMaster()
    {
        return $this->belongsTo(USD_defaultmaster::class,'usd_defaultMaster_id','id');
    }
}