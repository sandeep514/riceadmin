<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebUserSubscriptionModel extends Model
{
    protected $table = 'web_user_subscription';
    protected $fillable = ['user_id' ,'plan_id' ,'period_start','period_end','payment_id','order_id' ,'subscription_type' ,'status'];

}