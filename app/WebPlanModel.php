<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebPlanModel extends Model
{
    protected $table = 'web_plan';
    protected $fillable = ['title' ,'short_description' ,'description' ,'amount' ,'discount_percentage' ,'is_INR' ,'is_USD' ,'status'];

    public function getPlanKeyMap()
    {
        return $this->hasMany(WebPlanKeysMapModel::class , 'plan_id' , 'id');
    }
}