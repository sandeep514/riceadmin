<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DomesticVendorState extends Model
{
    protected $table = 'domestic_vendor_states';

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    protected $fillable = [
        'country_id',
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'country_id' => 'integer',
        'status' => 'integer',
    ];

    public function country()
    {
        return $this->belongsTo(DomesticVendorCountry::class, 'country_id', 'id');
    }

    public function cities()
    {
        return $this->hasMany(DomesticVendorCity::class, 'state_id', 'id');
    }

    public static function options(?int $countryId = null, ?int $includeId = null): array
    {
        return self::query()
            ->when($countryId, fn ($query) => $query->where('country_id', $countryId))
            ->where(function ($query) use ($includeId) {
                $query->where('status', self::STATUS_ACTIVE);
                if ($includeId) {
                    $query->orWhere('id', $includeId);
                }
            })
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
