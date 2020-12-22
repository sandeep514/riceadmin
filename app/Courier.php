<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    protected $fillable = ['date','samples','sent_via','details'];

    public static $sentVia = [
        1 => 'Train',
        2 => 'Bus',
        3 => 'Courier',
        4 => 'By Hand'
    ];
}
