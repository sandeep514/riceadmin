<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WandModel extends Model
{
    protected $table = "wand";
    protected $fillable = [ 'RiceNameId','wandTypeId' , 'value' , 'status' ];

    public function getWandType()
    {
        return $this->hasMany(WandTypeModel::class , 'id' , 'wandTypeId');
    }
}
