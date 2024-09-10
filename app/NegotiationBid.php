<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NegotiationBid extends Model
{
    protected $fillable = ['bid_id','vendor_id','buyer_id','negotiation_amount','status'];
    protected $table = "negotiation_bid";
}