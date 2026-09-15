<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VendorDestinationCountry extends Model
{
    protected $table = 'vendor_destination_countries';

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    protected $fillable = [
        'region_id',
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'region_id' => 'integer',
        'status' => 'integer',
    ];

    public function region()
    {
        return $this->belongsTo(VendorDestinationRegion::class, 'region_id', 'id');
    }

    public function destinationPorts()
    {
        return $this->hasMany(VendorDestinationPort::class, 'country_id', 'id');
    }

    public static function options(?int $regionId = null, ?int $includeId = null): array
    {
        return self::query()
            ->when($regionId, fn ($query) => $query->where('region_id', $regionId))
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
