<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BrandAvailability extends Model
{
    protected $table = "brand_availability";
    protected $fillable = [ 'branch_id' , 'state_id' , 'city_id' , 'status'];

    public function state_rel()
    {
        return $this->belongsTo(WebStates::class , 'state_id', 'id');
    }

    public function city_rel()
    {
        return $this->belongsTo(WebCities::class , 'city_id', 'id');
    }
}
