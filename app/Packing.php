<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Packing extends Model
{
    protected $fillable = ['code','value'];
    public static $millerPacking = [
        "50 kg PP",
        "55 kg PP"
    ];
    public static function packings(){
        return self::get();
//        return self::pluck('code','value');
    }
}
