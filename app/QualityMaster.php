<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\WandModel;


class QualityMaster extends Model
{
    protected $table = "quality_master";
    protected $fillable = ['quality','quality_name', 'quality_type' ,'quality_type_status' ,'status'];

    public function wand()
    {
        return $this->hasMany(WandModel::class , 'RiceNameId', 'id' );
    }
}
