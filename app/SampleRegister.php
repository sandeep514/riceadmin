<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SampleRegister extends Model
{
    protected $fillable = ['date','sntc_no','supplier','quality','packing','packing_type','seller_qty',
                            'seller_offer','photo','no_of_bags','qty_per_bag'];

    public function supplier_rel(){
        return $this->belongsTo(User::class,'supplier','id')->where('role',4);
    }

    public function quality_rel(){
        return $this->belongsTo(Quality::class,'quality','id');
    }

    public function packing_rel(){
        return $this->belongsTo(Packing::class,'packing','id');
    }

    public function packing_type_rel(){
        return $this->belongsTo(PackingType::class,'packing_type','id');
    }

    public static function registered_samples(){
        $nosArray = [];
        $sntcNos = self::pluck('sntc_no','sntc_no');
        foreach($sntcNos as $key => $no){
            $nosArray[$key] = str_pad($no, 5,0,STR_PAD_LEFT);
        }
        return $nosArray;
    }
}
