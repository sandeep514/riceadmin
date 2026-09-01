<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PackingType extends Model
{
    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public static function packingTypes()
    {
        return self::pluck('name', 'id');
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
