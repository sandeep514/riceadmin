<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Sample extends Model
{
    protected $fillable = ['date','supplier','quality','packing','packing_type','qty','photo','no_of_bags','bags_qty','user_id'];

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

    public static function samples(){
        $samples = self::with(['supplier_rel','quality_rel','packing_rel','packing_type_rel'])->get();
        $samplesArray = [];
        foreach($samples as $key => $sample){
            $samplesArray[$sample->id] = $sample->supplier_rel->name.' ('.$sample->quality_rel->name.')';
        }
        return $samplesArray;
    }

    public static function noOfBags(){
        return [
            'manual' => 'Enter Manually',
            'running' => 'Running'
        ];
    }
}
