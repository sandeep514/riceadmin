<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiceType extends Model
{
    protected $fillable = ['name'];

   	public static function riceTypes(){
        return self::pluck('name','id');
    }
}