<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BrandInterestLocation extends Model
{
    protected $table = 'brand_interest_locations';

    protected $fillable = [
        'brand_interest_id',
        'state_id',
        'city_id',
        'state_name',
        'city_name',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function brandInterest()
    {
        return $this->belongsTo(BrandInterest::class, 'brand_interest_id');
    }
}
