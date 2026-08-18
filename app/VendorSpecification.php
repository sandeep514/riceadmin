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
        'spec_for_id',
        'status',
    ];

    public function specFor()
    {
        return $this->belongsTo(VendorSpecFor::class, 'spec_for_id');
    }

    public function getSpecForLabelAttribute(): string
    {
        return $this->specFor->name ?? '—';
    }
}
