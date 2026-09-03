<?php

namespace App;

use Carbon\Carbon;
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
        'additional_information',
        'lot_number',
        'crop_year',
        'status',
        'is_new',
        'sold_at_amount',
        'sold_at',
        'created_by',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
        'is_new' => 'integer',
    ];

    /**
     * Individual trade status (admin).
     * 1 Active, 4 In-Process, 12 Hold, 3 Sold, 5 Deactivated, 2 Expired
     */
    public static $statusLabels = [
        1 => 'Active',
        4 => 'In-Process',
        12 => 'Hold',
        3 => 'Sold',
        5 => 'Deactivated',
        2 => 'Expired',
        0 => 'Closed', // legacy
    ];

    public static $statusBadgeClass = [
        1 => 'success',
        4 => 'info',
        12 => 'warning',
        3 => 'primary',
        5 => 'default',
        2 => 'danger',
        0 => 'default',
    ];

    /** Statuses shown on app/web portal list by default (excludes Expired / Deactivated) */
    public static $listableStatuses = [1, 4, 12, 3];

    /**
     * Statuses that can still auto-expire when valid_days has passed.
     */
    public static $expirableStatuses = [1, 4, 12];

    /**
     * Mark paddy trades past valid_days as expired (status = 2).
     * Same pattern as rice TradeQueriesINR expirePastValidDayTrades().
     * Parses valid_days with Carbon so free-text / display formats still expire correctly.
     */
    public static function expirePastValidDayTrades(): Carbon
    {
        $now = Carbon::now(config('app.timezone', 'Asia/Kolkata'));

        $candidates = static::query()
            ->whereIn('status', self::$expirableStatuses)
            ->whereNotNull('valid_days')
            ->where('valid_days', '!=', '')
            ->get(['id', 'valid_days']);

        $expireIds = [];
        foreach ($candidates as $row) {
            try {
                if (Carbon::parse((string) $row->valid_days)->lte($now)) {
                    $expireIds[] = (int) $row->id;
                }
            } catch (\Throwable $e) {
                // Unparseable valid_days — leave status unchanged
            }
        }

        if ($expireIds !== []) {
            static::query()
                ->whereIn('id', $expireIds)
                ->update(['status' => 2]);
        }

        return $now;
    }

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

    public function getIsNewLabelAttribute(): string
    {
        return ((int) $this->is_new === 1) ? 'Yes' : 'No';
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return asset('uploads/' . ltrim($this->image, '/'));
    }
}
