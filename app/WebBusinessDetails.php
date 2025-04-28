<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebBusinessDetails extends Model
{
    protected $table = 'web_business_details';
    protected $fillable = ['user_id','company_name','designation','address','registered_email','phone','locality','landmark','state','city','selected_category','status'];
}