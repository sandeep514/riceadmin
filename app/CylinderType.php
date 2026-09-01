<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CylinderType extends Model
{
    protected $table = 'cylinder_types';

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    protected $fillable = [
        'type',
        'description',
        'status',
        'order_no',
    ];

    public static function options(?int $includeId = null): array
    {
        return self::query()
            ->where(function ($query) use ($includeId) {
                $query->where('status', self::STATUS_ACTIVE);
                if ($includeId) {
                    $query->orWhere('id', $includeId);
                }
            })
            ->orderByRaw('order_no IS NULL, order_no ASC')
            ->orderBy('type')
            ->pluck('type', 'id')
            ->toArray();
    }
}
