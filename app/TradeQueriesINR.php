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

    protected $fillable = ['tradeFor','queryId','farmingType','quality_type','quality','qualityForm' ,'qualityFormLinkWithLivePrice','stateLinkWithLivePrice' ,'grade','packing','quantity','offerPrice','validDays','packing_file','packingStreamType','uncooked_file','uncooked_file1','uncooked_file2','uncooked_file3','cooked_file','cooked_file1','cooked_file2','cooked_file3','additioanlInfo','personal_remarks','location','crop','hotdeal','tradeType','moisture','kett','broken','dd','admixture','elongation','riceSize','sntcLotNo','sold_at','status','role_id','category_id'];

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
        1 => 'Full Grain',
        2 => 'Broken',
        3 => 'Sizer',
        4 => 'Resort',
    ];

    public static $farmingType = [
        1 => 'Conventional',
        2 => 'compliance/organic',
    ];

    /** Web portal / trade filter farming types. */
    public static $farmingTypeWeb = [
        1 => 'Conventional',
        2 => 'Organic',
        3 => 'EU Standards',
        4 => 'US Standards',
    ];

    /**
     * Human-readable farming type for API responses (web labels, then legacy admin labels).
     */
    public static function resolveFarmingName($farmingType): ?string
    {
        $key = (int) ($farmingType ?? 0);
        if ($key <= 0) {
            return null;
        }

        return static::$farmingTypeWeb[$key] ?? static::$farmingType[$key] ?? null;
    }

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
    public function RiceFormData()
    {
        return $this->belongsTo(RiceForm::class , 'qualityFormLinkWithLivePrice', 'id');
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

    public function tradeCategoryMaps()
    {
        return $this->hasMany(TradeCategoryMap::class, 'trade_id', 'id');
    }
}
