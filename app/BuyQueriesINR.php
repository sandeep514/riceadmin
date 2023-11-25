<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\RiceFormMilestone3;
use App\QualityMaster;
use App\WandModel;
use App\SellerPackingINR;

class BuyQueriesINR extends Model
{
    protected $table = 'buy_query_milestone3';
    protected $fillable = ['quality_type','quality','quality_form','grade','packing_type','packing','quantity','additional_info','status'];

    public static $status = [
        0 => 'closed',
        1 => 'Pending',
        2 => 'Moved to trade'
    ];

    public static $packingTypeStaus = [
        0 => 'Miller',
        1 => 'Private'
    ];

    public function RiceFormMilestone3()
    {
        return $this->belongsTo(RiceFormMilestone3::class , 'quality_form', 'id');
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
}