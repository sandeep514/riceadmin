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
        'created_by',
    ];

    public static $statusLabels = [
        0 => 'Closed',
        1 => 'Active',
    ];

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
