<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    protected $table = 'bid';
    protected $fillable = ['query_id','seller_id','bid_amount','validTill','counter_amount' , 'counter_status','status'];
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id', 'id' );
    }
    public function buyerQuery()
    {
        return $this->belongsTo(BuyQuery::class, 'query_id', 'id' );
    }
}