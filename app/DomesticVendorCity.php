<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DomesticVendorCity extends Model
{
    protected $table = 'domestic_vendor_cities';

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    protected $fillable = [
        'state_id',
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'state_id' => 'integer',
        'status' => 'integer',
    ];

    public function state()
    {
        return $this->belongsTo(DomesticVendorState::class, 'state_id', 'id');
    }

    public static function options(?int $stateId = null, ?int $includeId = null): array
    {
        return self::query()
            ->when($stateId, fn ($query) => $query->where('state_id', $stateId))
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
