<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CityZone extends Model
{
    protected $fillable = ['zone_area','city'];

    public function city_rel(){
        return $this->belongsTo(City::class,'city','id');
    }

    public static function zones(){
        return self::pluck('zone_area','id');
    }
}
