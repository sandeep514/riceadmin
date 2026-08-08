<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaddyTrade extends Model
{
    protected $table = 'paddy_trades';

    protected $fillable = [
        'paddy_sell_query_id',
        'category',
        'quality',
        'quality_name',
        'hand_combined',
        'packing_id',
        'packing',
        'contact_number',
        'contact_person',
        'image',
        'location',
        'quantity',
        'rate',
        'valid_days',
        'type',
        'user_id',
        'remarks',
        'status',
        'sold_at_amount',
        'sold_at',
        'created_by',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
    ];

    /**
     * Individual trade status (admin).
     * 1 Active, 4 In-Process, 12 Hold, 3 Sold, 5 Deactivated
     */
    public static $statusLabels = [
        1 => 'Active',
        4 => 'In-Process',
        12 => 'Hold',
        3 => 'Sold',
        5 => 'Deactivated',
        0 => 'Closed', // legacy
    ];

    public static $statusBadgeClass = [
        1 => 'success',
        4 => 'info',
        12 => 'warning',
        3 => 'primary',
        5 => 'default',
        0 => 'default',
    ];

    /** Statuses shown on app/web portal list by default */
    public static $listableStatuses = [1, 4, 12, 3];

    public function getStatusBadgeClassAttribute(): string
    {
        return self::$statusBadgeClass[(int) $this->status] ?? 'default';
    }

    public function paddySellQuery()
    {
        return $this->belongsTo(PaddySellQuery::class, 'paddy_sell_query_id');
    }

    public function paddyQuality()
    {
        return $this->belongsTo(PaddyQuality::class, 'quality', 'id');
    }

    public function packingRel()
    {
        return $this->belongsTo(SellerPackingINR::class, 'packing_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getPackingLabelAttribute(): string
    {
        return optional($this->packingRel)->packing
            ?? ($this->packing ?: '-');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function getCategoryLabelAttribute(): string
    {
        return PaddyQuality::riceTypeOptions()[$this->category] ?? ($this->category ?: '-');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[(int) $this->status] ?? (string) $this->status;
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return asset('uploads/' . ltrim($this->image, '/'));
    }
}
