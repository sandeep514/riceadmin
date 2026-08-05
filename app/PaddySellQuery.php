<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaddySellQuery extends Model
{
    protected $table = 'paddy_sell_queries';

    protected $fillable = [
        'category',
        'quality',
        'quality_name',
        'hand_combined',
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
        'status',
    ];

    public static $statusLabels = [
        0 => 'Closed',
        1 => 'Pending',
    ];

    public function paddyQuality()
    {
        return $this->belongsTo(PaddyQuality::class, 'quality', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getCategoryLabelAttribute(): string
    {
        $map = [
            'basmati' => 'Basmati',
            'non-basmati' => 'Non Basmati',
        ];

        return $map[$this->category] ?? ($this->category ?: '-');
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
