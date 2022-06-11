<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use App\HotDealAccept;
use App\USD_defaultmaster;
use App\QualityMaster;

class HotDealNotification extends Model
{
    public $table = "hotdeals";
    protected $fillable = [ 'title','quality','packing','fob','qty','validdate','message','status' ];

    public function HotDealAccept()
    {
        return $this->hasMany(HotDealAccept::class , 'hotdeal_id' , 'id' );
    }
    public function getUSDDefaultMaster()
    {
        return $this->belongsTo(USD_defaultmaster::class,'packing','id');
    }
    public function getRiceQuality()
    {
        return $this->belongsTo(QualityMaster::class,'quality','id');
    }
}