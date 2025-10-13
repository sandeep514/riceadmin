<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebPersonalDetails extends Model
{
    protected $table = 'web_personal_details';
    protected $fillable = ['user_id','firstname','lastname','avatar','email','phone_number','state','district','address','farmer_unique_id','pan_card','status'];   
}