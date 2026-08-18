<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VendorContainerParticular extends Model
{
    protected $table = 'vendor_container_particulars';

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    protected $fillable = [
        'particular',
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
            ->orderBy('particular')
            ->pluck('particular', 'id')
            ->toArray();
    }
}
