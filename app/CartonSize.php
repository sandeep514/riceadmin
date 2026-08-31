<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CartonSize extends Model
{
    protected $table = 'carton_sizes';

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    protected $fillable = [
        'size',
        'description',
        'status',
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
            ->orderBy('size')
            ->pluck('size', 'id')
            ->toArray();
    }
}
