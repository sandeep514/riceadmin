<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = ['name','country_id'];

    public static function statesList(){
        return self::pluck('name','id');
    }
}
