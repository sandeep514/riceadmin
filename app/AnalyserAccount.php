<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AnalyserAccount extends Model
{
    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'received_amount',
        'contact_person_name',
        'contact_mobile',
        'has_historical_access',
        'download_limit',
        'downloads_used',
        'has_today_access',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'has_historical_access' => 'boolean',
        'has_today_access' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function analyserRoleId(): ?int
    {
        $role = Role::where('role_name', 'analyser')->first();

        return $role ? (int) $role->id : null;
    }

    /**
     * Active when the linked user is enabled and today is within [start_date, end_date].
     */
    public function isActive(): bool
    {
        $user = $this->user;
        if (! $user || (int) ($user->status ?? 0) !== 1 || $user->isDeactivated()) {
            return false;
        }

        $today = Carbon::today();
        if ($this->start_date && $today->lt($this->start_date->copy()->startOfDay())) {
            return false;
        }
        if ($this->end_date && $today->gt($this->end_date->copy()->endOfDay())) {
            return false;
        }

        return true;
    }

    public function canViewHistorical(): bool
    {
        return $this->isActive() && (bool) $this->has_historical_access;
    }

    public function canViewToday(): bool
    {
        return $this->isActive() && (bool) $this->has_today_access;
    }

    public function downloadsRemaining(): ?int
    {
        if ($this->download_limit === null) {
            return null; // unlimited
        }

        return max(0, (int) $this->download_limit - (int) $this->downloads_used);
    }

    public function canDownload(): bool
    {
        if (! $this->canViewHistorical()) {
            return false;
        }

        $remaining = $this->downloadsRemaining();

        return $remaining === null || $remaining > 0;
    }

    /**
     * Atomically consume one download. Returns false when no quota left.
     */
    public function recordDownload(): bool
    {
        if (! $this->canViewHistorical()) {
            return false;
        }

        if ($this->download_limit === null) {
            $this->increment('downloads_used');

            return true;
        }

        $updated = static::where('id', $this->id)
            ->whereRaw('downloads_used < download_limit')
            ->increment('downloads_used');

        if ($updated) {
            $this->refresh();
        }

        return (bool) $updated;
    }
}
