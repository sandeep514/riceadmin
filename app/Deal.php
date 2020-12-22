<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    protected $fillable = ['date','sntc_no','contract_no','buyer','quantity','seller','is_direct_deal','document','user_id'];

    public function seller_rel(){
        return $this->belongsTo(User::class,'seller','id');
    }

    public function buyer_rel(){
        return $this->belongsTo(User::class,'buyer','id');
    }

    public function quality_rel(){
        return $this->belongsTo(Quality::class,'quality','id');
    }

    public static function contracts(){
        return self::pluck('contract_no','id');
    }
}
