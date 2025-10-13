<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\RiceFormMilestone3;
use App\RiceName;
use App\QualityMaster;
use App\WandModel;
use App\SellerPackingINR;
use App\Buyerpackinginr;
use App\TradeLike;

class TradeQueriesINR extends Model
{
    protected $table = 'trade_query_milestone3';

    protected $fillable = ['tradeFor','queryId','farmingType','quality_type','quality','qualityForm','grade','packing','quantity','offerPrice','validDays','packing_file','uncooked_file','uncooked_file1','uncooked_file2','uncooked_file3','cooked_file','cooked_file1','cooked_file2','cooked_file3','additioanlInfo','location','crop','hotdeal','tradeType','moisture','kett','broken','dd','admixture','elongation','riceSize','status'];

    public static $tradeStatus = [ 
        3 => "sold", 
        2 => 'expired' , 
        1 => 'Pending',
        6 => 'Active',
        4 => 'In-Process',
        5 => 'De-active',
        11 => 'close', 
        12 => 'hold'
    ];



    public static $tradeType = [ 
        1 => 'Buy', 
        2 => 'Sell' , 
        3 => 'Future Buying',
        4 => 'Future Selling'
    ];
    public static $riceSize = [ 
        1 => 'Full Grane',
        2 => 'Broken',
        3 => 'Sizer',
        4 => 'Resort',
    ];
    public static $farmingType = [ 
        1 => 'Conventional',
        2 => 'compliance/organic'
    ];

    public function RiceFormMilestone3()
    {
        return $this->belongsTo(RiceFormMilestone3::class , 'qualityForm', 'id');
    }

    public function RiceQualityMaster()
    {
        return $this->belongsTo(QualityMaster::class , 'quality', 'id');
    }
    public function RiceNameData()
    {
        return $this->belongsTo(RiceName::class , 'quality', 'id');
    }

    public function riceGrade()
    {
        return $this->belongsTo(WandModel::class , 'grade', 'id');
    }

    public function RicePacking()
    {
        return $this->belongsTo(SellerPackingINR::class , 'packing', 'id');
    }
    public function RicePackingBuyer()
    {
        return $this->belongsTo(Buyerpackinginr::class , 'packing', 'id');
    }
    public function RicePackingSeller()
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