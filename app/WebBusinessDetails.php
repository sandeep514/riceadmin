<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Category;

class WebBusinessDetails extends Model
{
    protected $table = 'web_business_details';
    protected $fillable = ['user_id','company_name','product','contactPerson','contactMobile','designation','address','registered_email','phone','locality','landmark','state','city','selected_category','status'];


    public function getCategoryDetails()
    {
        return $this->belongsTo(Category::class ,'selected_category' , 'id');
    }

    public function getBagVendorWeb()
    {
        return $this->belongsTo(WebOtherServiceProvider::class ,'selected_category' , 'id');
    }

    public function cityRel()
    {
        return $this->belongsTo(WebCities::class , 'city' ,'id');
    }

    public function stateRel()
    {
        return $this->belongsTo(WebStates::class , 'state' ,'id');
    }

}