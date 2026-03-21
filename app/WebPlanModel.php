<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebPlanModel extends Model
{
    protected $table = 'web_plan';
    protected $fillable = [
        'title',
        'role_id',
        'category_id',
        'short_description',
        'description',
        'amount',
        'discount_percentage',
        'monthly_price',
        'quarterly_price',
        'yearly_price',
        'monthly_final_amount',
        'quarterly_final_amount',
        'yearly_final_amount',
        'monthly_discount_percentage',
        'quarterly_discount_percentage',
        'yearly_discount_percentage',
        'monthly_gst',
        'quarterly_gst',
        'yearly_gst',
        'is_INR',
        'is_USD',
        'status'
    ];

    public function getPlanKeyMap()
    {
        return $this->hasMany(WebPlanKeysMapModel::class , 'plan_id' , 'id');
    }
}
