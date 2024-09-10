<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\RiceFormMilestone3;
use App\QualityMaster;
use App\WandModel;
use App\SellerPackingINR;
use App\TradeLike;

class TradeCurrentStatus extends Model
{
    protected $table = 'trade_current_status';
    protected $fillable = ['trade_id','currentStatus'];
}