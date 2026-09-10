<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VendorContainerSize extends Model
{
    protected $table = 'vendor_container_sizes';

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    protected $fillable = [
        'size',
        'label',
        'description',
        'status',
    ];

    protected $casts = [
        'size' => 'integer',
        'status' => 'integer',
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
            ->get()
            ->mapWithKeys(function (self $row) {
                $label = $row->label ?: ($row->size.' FT');

                return [$row->id => $label];
            })
            ->toArray();
    }
}
