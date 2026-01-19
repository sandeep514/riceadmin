<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebCities extends Model
{
    protected $table = "web_cities";
    protected $fillable = ['city_name' , 'state_id' ,'is_capital' , 'population'];
}