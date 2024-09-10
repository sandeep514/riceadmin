<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['city_name'];

    public static function cities(){
        return collect(self::pluck('city_name','id'))->put(0,'Other');
    }

    public static function userCities(){
        return self::pluck('city_name','id');
    }
}
