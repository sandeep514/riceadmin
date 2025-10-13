<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\RiceFormMilestone3;
use App\QualityMaster;
use App\WandModel;
use App\User;
use App\SellerPackingINR;
use App\Buyerpackinginr;

class FutureBuyQueriesINR extends Model
{
    protected $table = 'future_buy_query_milestone3';
    protected $fillable = ["selectedQualityTypeInt","year","quality","quality_form","grade","packing","packing_type","warehouselocation","contactperson","contactMobile","userId","expectedPackingSchedule","bagStatus","expectedBagDelivery","quantity","offerPrice","created_by","validDays","uncookedFile","cookedImageFile",'farming','contactPerson','contactMobile','status'];

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
        return $this->belongsTo(Buyerpackinginr::class , 'packing', 'id');
    }
    public function UserDetail()
    {
        return $this->belongsTo(User::class , 'created_by', 'id');
    }
}