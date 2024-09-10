<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OceanFreight extends Model
{
    protected $fillable = ['sno','region','country','port','freight_21MT','freight_25MT','freight_21MT-1MT','freight_25MT-1MT'];
    protected $table = 'ocean_freight';

}
