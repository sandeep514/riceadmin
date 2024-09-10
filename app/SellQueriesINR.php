<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\RiceFormMilestone3;
use App\QualityMaster;
use App\WandModel;
use App\SellerPackingINR;
use App\User;

class SellQueriesINR extends Model
{
    protected $table = 'sell_query_milestone3';
    protected $fillable = ['quality_type','quality','qualityForm','grade','packing','quantity','offerPrice','validDays','packing_file','uncooked_file','cooked_file','contactperson','contactMobile','warehouselocation','created_by','status'];

    public static $status = [
        0 => 'closed',
        1 => 'Pending',
        2 => 'Moved to trade'
    ];

    public function RiceFormMilestone3()
    {
        return $this->belongsTo(RiceFormMilestone3::class , 'qualityForm', 'id');
    }

    public function RiceQualityMaster()
    {
        return $this->belongsTo(QualityMaster::class , 'quality', 'id');
    }
    public function RiceQualityRiceNames()
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
    public function UserDetail()
    {
        return $this->belongsTo(User::class , 'created_by', 'id');
    }
}