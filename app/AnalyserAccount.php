<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AnalyserAccount extends Model
{
    /** Display / DB role_name for this product role. */
    public const ROLE_NAME = 'Data Analyst';

    /** Stable technical role type (kept as analyser for APIs / maps). */
    public const ROLE_TYPE = 'analyser';

    /**
     * Legacy + current role_name values that identify this role.
     *
     * @return list<string>
     */
    public static function roleNameAliases(): array
    {
        return [
            'data analyst',
            'analyser',
            'analyzer',
            'market analyst',
        ];
    }

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
        $role = self::resolveAnalyserRole();

        return $role ? (int) $role->id : null;
    }

    public static function resolveAnalyserRole(): ?Role
    {
        $aliases = self::roleNameAliases();

        // Prefer the canonical display name first.
        $byCanonical = Role::query()
            ->whereRaw('LOWER(role_name) = ?', [strtolower(self::ROLE_NAME)])
            ->orderBy('id')
            ->first();
        if ($byCanonical) {
            return $byCanonical;
        }

        $byName = Role::query()
            ->where(function ($q) use ($aliases) {
                foreach ($aliases as $alias) {
                    $q->orWhereRaw('LOWER(role_name) = ?', [$alias]);
                }
            })
            ->orderBy('id')
            ->first();

        if ($byName) {
            return $byName;
        }

        return Role::query()
            ->where('type', self::ROLE_TYPE)
            ->orderBy('id')
            ->first();
    }

    /**
     * Role ids that may hold Data Analyst category maps (current + legacy names).
     *
     * @return list<int>
     */
    public static function categoryMapRoleIds(): array
    {
        $aliases = self::roleNameAliases();

        $ids = Role::query()
            ->where(function ($q) use ($aliases) {
                foreach ($aliases as $alias) {
                    $q->orWhereRaw('LOWER(role_name) = ?', [$alias]);
                }
                $q->orWhere('type', self::ROLE_TYPE);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $primary = self::analyserRoleId();
        if ($primary && ! in_array($primary, $ids, true)) {
            array_unshift($ids, $primary);
        }

        return $ids;
    }

    /**
     * Active category ids mapped for analyser (Role–Category Map).
     * Falls back to all active categories when no map rows exist.
     *
     * @return list<int>
     */
    public static function allowedCategoryIds(): array
    {
        $roleIds = self::categoryMapRoleIds();
        $mapped = [];

        if ($roleIds !== []) {
            $mapQuery = CategoryRoleMap::query()->whereIn('role', $roleIds);
            if (Schema::hasColumn('category_role_map', 'status')) {
                // Older rows may have null status — treat as active.
                $mapQuery->where(function ($q) {
                    $q->where('status', 1)->orWhereNull('status');
                });
            }
            $mapped = $mapQuery->pluck('category')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        $categoryQuery = Category::query()->where(function ($q) {
            $q->where('status', 1)->orWhereNull('status');
        });

        if ($mapped !== []) {
            $categoryQuery->whereIn('id', $mapped);
        }

        return $categoryQuery
            ->orderByRaw('COALESCE(`order`, 999999)')
            ->orderBy('category')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @return array<int, string> id => category name
     */
    public static function categoryOptions(): array
    {
        $ids = self::allowedCategoryIds();
        if ($ids === []) {
            return [];
        }

        return Category::query()
            ->whereIn('id', $ids)
            ->orderByRaw('COALESCE(`order`, 999999)')
            ->orderBy('category')
            ->pluck('category', 'id')
            ->toArray();
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
