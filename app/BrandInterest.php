<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandInterest extends Model
{
    use HasFactory;

    protected $table = 'brand_interest';

    // Primary key (optional if 'id')
    protected $primaryKey = 'id';

    // Timestamps (if your table uses created_at and updated_at)
    public $timestamps = true;

    // Mass assignable fields
    protected $fillable = [
        'user_id',
        'brand_id',
        'contact_person_name',
        'contact_person_number',
        'basmati_monthly',
        'non_basmati_monthly',
        'status',
    ];

    // Casts (optional, useful for type conversion)
    protected $casts = [
        'status' => 'boolean',
    ];

    public function locations()
    {
        return $this->hasMany(BrandInterestLocation::class, 'brand_interest_id');
    }

    public function brandInterestMaps()
    {
        return $this->hasMany(BrandInterestMap::class, 'brand_interest_id');
    }
}
