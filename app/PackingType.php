<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PackingType extends Model
{
    protected $fillable = ['name'];

    public static function packingTypes(){
        return self::pluck('name','id');
    }
}
