<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Domestictransport extends Model
{
    protected $table = 'domestictransport';
    protected $fillable = ['from','to','upto','pmt','status'];
}