<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Bid;
use App\USD_defaultmaster;

class BuyQuery extends Model
{
    protected $table = 'buy_query';
    protected $fillable = ['PackingType','mobile','partyName','portName','qualityName','quantity','remarks','qualityType','validDays','validDate','user' , 'status'];


    public function getBids()
    {
        return $this->hasMany(Bid::class , 'query_id', 'id' );
    }

    public function getPackingType()
    {
        return $this->belongsTo(USD_defaultmaster::class , 'PackingType', 'id');
    }
    
}