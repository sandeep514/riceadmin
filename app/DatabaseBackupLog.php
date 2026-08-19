<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class DatabaseBackupLog extends Model
{
    public const OVERDUE_AFTER_DAYS = 5;

    protected $table = 'database_backup_logs';

    protected $fillable = [
        'user_id',
        'filename',
        'downloaded_at',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    public static function lastDownload(): ?self
    {
        if (! Schema::hasTable((new self)->getTable())) {
            return null;
        }

        return self::query()->orderByDesc('downloaded_at')->first();
    }

    public static function daysSinceLastDownload(?self $last = null): ?int
    {
        $last = $last ?? self::lastDownload();
        if ($last === null || $last->downloaded_at === null) {
            return null;
        }

        return (int) $last->downloaded_at->copy()->startOfDay()->diffInDays(Carbon::now()->startOfDay());
    }

    public static function isOverdue(?self $last = null): bool
    {
        $last = $last ?? self::lastDownload();
        if ($last === null) {
            return Schema::hasTable((new self)->getTable());
        }

        $days = self::daysSinceLastDownload($last);

        return $days !== null && $days >= self::OVERDUE_AFTER_DAYS;
    }
}
