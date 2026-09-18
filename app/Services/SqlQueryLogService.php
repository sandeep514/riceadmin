<?php

namespace App\Services;

use App\SqlQueryLog;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SqlQueryLogService
{
    public const APP_THRESHOLD_MS = 500;
    public const PROCESSLIST_THRESHOLD_SEC = 2;
    public const SQL_LIMIT = 1600;
    public const KEEP_ROWS = 500;

    private static bool $writing = false;

    public function logFromAppQuery(QueryExecuted $query): void
    {
        if (self::$writing || $query->time < self::APP_THRESHOLD_MS) {
            return;
        }

        $sql = $this->interpolateSql($query->sql, $query->bindings);
        if ($this->shouldSkip($sql)) {
            return;
        }

        $this->store([
            'source' => 'app',
            'duration_ms' => (int) round($query->time),
            'cpu_percent' => $this->lastCpuPercent(),
            'sql_text' => $sql,
            'db_name' => $query->connection->getDatabaseName(),
            'connection' => $query->connectionName,
            'request_path' => $this->currentPath(),
            'mysql_thread_id' => null,
        ]);
    }

    public function logFromRunningQueries(array $runningQueries, ?float $cpuPercent = null): void
    {
        foreach ($runningQueries as $row) {
            $timeSec = (int) ($row['time_sec'] ?? 0);
            if ($timeSec < self::PROCESSLIST_THRESHOLD_SEC) {
                continue;
            }

            $sql = (string) ($row['sql'] ?? '');
            if ($this->shouldSkip($sql)) {
                continue;
            }

            $this->store([
                'source' => 'processlist',
                'duration_ms' => $timeSec * 1000,
                'cpu_percent' => $cpuPercent !== null ? (int) round($cpuPercent) : $this->lastCpuPercent(),
                'sql_text' => $sql,
                'db_name' => $row['db'] ?? null,
                'connection' => trim((string) (($row['user'] ?? '') . '@' . ($row['host'] ?? '')), '@'),
                'request_path' => null,
                'mysql_thread_id' => isset($row['id']) ? (int) $row['id'] : null,
            ], 120);
        }
    }

    public function recent(int $limit = 50): array
    {
        if (! $this->tableReady()) {
            return [];
        }

        return SqlQueryLog::query()
            ->orderByDesc('created_at')
            ->orderByDesc('duration_ms')
            ->limit($limit)
            ->get([
                'id',
                'source',
                'duration_ms',
                'cpu_percent',
                'sql_text',
                'db_name',
                'connection',
                'request_path',
                'created_at',
            ])
            ->map(function (SqlQueryLog $row) {
                return [
                    'id' => $row->id,
                    'source' => $row->source,
                    'duration_ms' => (int) $row->duration_ms,
                    'duration_sec' => round(((int) $row->duration_ms) / 1000, 2),
                    'cpu_percent' => $row->cpu_percent,
                    'sql' => $row->sql_text,
                    'db_name' => $row->db_name,
                    'connection' => $row->connection,
                    'request_path' => $row->request_path,
                    'created_at' => optional($row->created_at)->format('Y-m-d H:i:s'),
                ];
            })
            ->all();
    }

    private function store(array $payload, int $dedupeSeconds = 30): void
    {
        if (! $this->tableReady()) {
            return;
        }

        $sql = $this->truncateSql((string) ($payload['sql_text'] ?? ''));
        if ($sql === '') {
            return;
        }

        $hash = md5(($payload['source'] ?? '') . '|' . ($payload['mysql_thread_id'] ?? '') . '|' . $sql);
        $cacheKey = 'sql_query_log.' . $hash;
        if (! Cache::add($cacheKey, 1, $dedupeSeconds)) {
            return;
        }

        self::$writing = true;
        try {
            SqlQueryLog::create([
                'source' => $payload['source'] ?? 'app',
                'duration_ms' => (int) ($payload['duration_ms'] ?? 0),
                'cpu_percent' => $payload['cpu_percent'] ?? null,
                'sql_hash' => $hash,
                'sql_text' => $sql,
                'db_name' => $payload['db_name'] ?? null,
                'connection' => $payload['connection'] ?? null,
                'request_path' => $payload['request_path'] ?? null,
                'mysql_thread_id' => $payload['mysql_thread_id'] ?? null,
                'created_at' => now(),
            ]);
            $this->prune();
        } catch (Throwable $e) {
            // Never break the original request because logging failed.
        } finally {
            self::$writing = false;
        }
    }

    private function prune(): void
    {
        $keep = self::KEEP_ROWS;
        $maxId = SqlQueryLog::query()->max('id');
        if (! $maxId || $maxId % 25 !== 0) {
            return;
        }

        $cutoffId = SqlQueryLog::query()
            ->orderByDesc('id')
            ->skip($keep)
            ->value('id');

        if ($cutoffId) {
            SqlQueryLog::query()->where('id', '<=', $cutoffId)->delete();
        }
    }

    private function shouldSkip(string $sql): bool
    {
        $haystack = strtolower($sql);

        return $haystack === ''
            || str_contains($haystack, 'sql_query_logs')
            || str_contains($haystack, 'information_schema.processlist')
            || str_contains($haystack, 'events_statements_summary_by_digest')
            || str_contains($haystack, 'show global status')
            || str_contains($haystack, 'show full processlist');
    }

    private function interpolateSql(string $sql, array $bindings): string
    {
        foreach ($bindings as $binding) {
            $sql = preg_replace('/\?/', $this->quoteBinding($binding), $sql, 1) ?? $sql;
        }

        return $sql;
    }

    private function quoteBinding($binding): string
    {
        if ($binding === null) {
            return 'NULL';
        }
        if (is_bool($binding)) {
            return $binding ? '1' : '0';
        }
        if (is_int($binding) || is_float($binding)) {
            return (string) $binding;
        }
        if ($binding instanceof \DateTimeInterface) {
            return "'" . $binding->format('Y-m-d H:i:s') . "'";
        }

        $value = (string) $binding;
        if (strlen($value) > 200) {
            $value = substr($value, 0, 200) . '…';
        }

        return "'" . str_replace("'", "''", $value) . "'";
    }

    private function truncateSql(string $sql): string
    {
        $sql = trim(preg_replace('/\s+/', ' ', $sql) ?? $sql);
        if (strlen($sql) <= self::SQL_LIMIT) {
            return $sql;
        }

        return substr($sql, 0, self::SQL_LIMIT) . '…';
    }

    private function currentPath(): ?string
    {
        try {
            $path = request()->method() . ' ' . request()->getPathInfo();

            return strlen($path) > 255 ? substr($path, 0, 255) : $path;
        } catch (Throwable $e) {
            return null;
        }
    }

    private function lastCpuPercent(): ?int
    {
        $cpu = Cache::get('server_insights.cpu_percent');

        return is_numeric($cpu) ? (int) round($cpu) : null;
    }

    private function tableReady(): bool
    {
        static $ready = null;
        if ($ready !== null) {
            return $ready;
        }

        try {
            $ready = Schema::hasTable('sql_query_logs');
        } catch (Throwable $e) {
            $ready = false;
        }

        return $ready;
    }
}
