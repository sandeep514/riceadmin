<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class RicePriceClosing extends Model
{
    protected $table = 'rice_price_closings';

    protected $fillable = [
        'close_date',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'close_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function forDate($date): ?self
    {
        $day = $date instanceof Carbon
            ? $date->copy()->timezone('Asia/Kolkata')->toDateString()
            : Carbon::parse($date, 'Asia/Kolkata')->toDateString();

        return self::query()->whereDate('close_date', $day)->first();
    }

    /**
     * Upcoming closings from today (IST), optionally limited.
     */
    public static function upcoming(?int $limit = null): Collection
    {
        $today = Carbon::now('Asia/Kolkata')->toDateString();

        $query = self::query()
            ->whereDate('close_date', '>=', $today)
            ->orderBy('close_date');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'date' => $this->close_date ? $this->close_date->format('Y-m-d') : null,
            'close_date' => $this->close_date ? $this->close_date->format('Y-m-d') : null,
            'reason' => (string) $this->reason,
            'display_date' => $this->close_date ? $this->close_date->format('d M Y') : null,
        ];
    }
}
