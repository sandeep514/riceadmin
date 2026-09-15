<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VendorDestinationRegion extends Model
{
    protected $table = 'vendor_destination_regions';

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function countries()
    {
        return $this->hasMany(VendorDestinationCountry::class, 'region_id', 'id');
    }

    public function destinationPorts()
    {
        return $this->hasMany(VendorDestinationPort::class, 'region_id', 'id');
    }

    public static function options(?int $includeId = null): array
    {
        return self::query()
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
