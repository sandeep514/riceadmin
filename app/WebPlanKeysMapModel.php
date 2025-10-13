<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebPlanKeysMapModel extends Model
{
    protected $table = 'web_plan_keys_map';
    protected $fillable = ['plan_id' ,'key_id' ,'value' ,'status'];

    public function getPlanKey()
    {
        return $this->belongsTo(WebPlanKeysModel::class , 'plan_id' , 'id');
    }

}
