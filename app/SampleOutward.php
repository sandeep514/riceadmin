<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SampleOutward extends Model
{
    protected $fillable = ['date','sntc_no','buyer','quality','bag_type','qty_per_bag','qty','awb_no','photo','no_of_bags'];

    public function buyer_rel(){
        return $this->belongsTo(User::class,'buyer','id');
    }

    public function quality_rel(){
        return $this->belongsTo(Quality::class,'quality','id');
    }

    public function packingType_rel(){
        return $this->belongsTo(PackingType::class,'bag_type','id');
    }
}
