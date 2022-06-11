<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class USD_defaultmaster extends Model
{
    protected $table = 'USD_defaultmaster';
    protected $fillable = ['order','bag_size','bag_type','bag_cost','local_freight','cha','bank_charges','ins','total','conversion_rate','PMT_USD','applied_for','status'];

    public function getUSDDefaultMaster()
    {
        return $this->hasMany(USD_prices::class,'usd_defaultMaster_id','id');
    }


}