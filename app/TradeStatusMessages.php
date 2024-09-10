<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\RiceFormMilestone3;
use App\QualityMaster;
use App\WandModel;
use App\SellerPackingINR;
use App\TradeLike;

class TradeStatusMessages extends Model
{
    protected $table = 'trade_status_message';
    protected $fillable = ['trade_status','message','status'];


    public function RiceFormMilestone3()
    {
        return $this->belongsTo(RiceFormMilestone3::class , 'qualityForm', 'id');
    }

    public function RiceQualityMaster()
    {
        return $this->belongsTo(QualityMaster::class , 'quality', 'id');
    }

    public function riceGrade()
    {
        return $this->belongsTo(WandModel::class , 'grade', 'id');
    }
    
    public function RicePacking()
    {
        return $this->belongsTo(SellerPackingINR::class , 'packing', 'id');
    }
    
    public function TradeLike()
    {
        return $this->belongsTo(TradeLike::class, 'id' , 'tradeId');
    }
    
    public function TradeLikeAll()
    {
        return $this->hasMany(TradeLike::class, 'tradeId', 'id' );
    }
    
    public function TradeInterest()
    {
        return $this->belongsTo(TradeIntrested::class, 'id' , 'tradeId');
    }

}