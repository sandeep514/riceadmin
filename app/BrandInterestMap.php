<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandInterestMap extends Model
{
    use HasFactory;

    protected $table = 'brand_interest_map';

    protected $fillable = [
        'brand_interest_id',
        'already_working_with_brand_name',
        'status',
    ];

    // Optional: if you want to hide timestamps in JSON response
    // protected $hidden = ['created_at', 'updated_at'];

    /**
     * Relationship: A BrandInterestMap belongs to a BrandInterest
     */
    public function brandInterest()
    {
        return $this->belongsTo(BrandInterest::class, 'brand_interest_id');
    }
}
