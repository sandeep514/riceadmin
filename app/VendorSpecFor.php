<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VendorSpecFor extends Model
{
    protected $table = 'vendor_spec_fors';

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function specifications()
    {
        return $this->hasMany(VendorSpecification::class, 'spec_for_id');
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
