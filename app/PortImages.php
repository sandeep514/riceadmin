<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PortImages extends Model
{
    public $table = 'port_images';
    protected $fillable = ['port' , 'attachment','status'];
}
