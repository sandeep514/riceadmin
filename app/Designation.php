<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $fillable = ['designation'];

    public static function designationsList(){
        return self::pluck('designation','id');
    }
}
