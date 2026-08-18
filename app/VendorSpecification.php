<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VendorSpecification extends Model
{
    protected $table = 'vendor_specifications';

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    protected $fillable = [
        'specification',
        'description',
        'spec_for',
        'status',
    ];

    public static function specForOptions(): array
    {
        return [
            'vendor' => 'Vendor',
            'product' => 'Product',
            'quality' => 'Quality',
            'packing' => 'Packing',
            'bag' => 'Bag',
            'service' => 'Service',
        ];
    }

    public function getSpecForLabelAttribute(): string
    {
        return self::specForOptions()[$this->spec_for] ?? ($this->spec_for ?: '-');
    }
}
