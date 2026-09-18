<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class ServerInsightsService
{
    private const SQL_LIMIT = 1600;

    public function collect(): array
    {
        $host = $this->hostMetrics();

        return [
            'collected_at' => now()->format('Y-m-d H:i:s'),
            'host' => $host,
            'mysql' => $this->mysqlMetrics($host['cpu_percent'] ?? null),
        ];
    }

    private function hostMetrics(): array
    {
        $load = function_exists('sys_getloadavg') ? sys_getloadavg() : false;
        $cores = $this->cpuCores();
        $cpuPercent = $this->hostCpuPercent();
        if ($cpuPercent !== null) {
            Cache::put('server_insights.cpu_percent', $cpuPercent, 180);
        }
        $memory = $this->hostMemory();
        $load1 = is_array($load) ? round((float) $load[0], 2) : null;
        $warnings = [];

        if ($cpuPercent !== null && $cpuPercent >= 85) {
            $warnings[] = 'Host CPU is high. Check running SQL below.';
        }
        if ($load1 !== null && $cores > 0 && $load1 >= $cores) {
            $warnings[] = 'Load average is at or above CPU core count.';
        }
        if (($memory['used_percent'] ?? null) !== null && $memory['used_percent'] >= 90) {
            $warnings[] = 'Host memory usage is high.';
        }

        return [
            'hostname' => gethostname() ?: php_uname('n'),
            'os' => php_uname('s') . ' ' . php_uname('r'),
            'load_1' => $load1,
            'load_5' => is_array($load) ? round((float) $load[1], 2) : null,
            'load_15' => is_array($load) ? round((float) $load[2], 2) : null,
            'cpu_percent' => $cpuPercent,
            'cpu_cores' => $cores,
            'memory' => $memory,
            'php_memory_mb' => round(memory_get_usage(true) / 1048576, 1),
            'warnings' => $warnings,
        ];
    }

    private function mysqlMetrics(?float $cpuPercent = null): array
    {
        $empty = [
            'available' => false,
            'error' => null,
            'version' => null,
            'threads_connected' => null,
            'threads_running' => null,
            'slow_queries' => null,
            'questions' => null,
            'uptime_sec' => null,
            'qps' => null,
            'tmp_disk_tables' => null,
            'max_used_connections' => null,
            'running_queries' => [],
            'top_statements' => [],
            'query_logs' => [],
            'warnings' => [],
            'notes' => [],
        ];

        try {
            $status = $this->mysqlStatusMap();
            $threadsRunning = $this->statusInt($status, 'Threads_running');
            $uptime = $this->statusInt($status, 'Uptime');
            $questions = $this->statusInt($status, 'Questions');
            $warnings = [];
            $notes = [];

            if ($threadsRunning !== null && $threadsRunning >= 8) {
                $warnings[] = 'MySQL has many threads running right now.';
            }

            $running = $this->runningQueries();
            $top = $this->topStatements($notes);
            $queryLogs = [];

            try {
                $logger = app(SqlQueryLogService::class);
                $logger->logFromRunningQueries($running, $cpuPercent ?? null);
                $queryLogs = $logger->recent(50);
            } catch (Throwable $e) {
                $notes[] = 'Could not persist heavy SQL log.';
            }

            if ($running === [] && $top === []) {
                $notes[] = 'If tables are empty, the DB user may need PROCESS and access to performance_schema.';
            }

            $notes[] = 'MySQL does not expose OS CPU% per query. Rankings use query time, executions, and rows examined — these are the usual CPU spikes.';

            return [
                'available' => true,
                'error' => null,
                'version' => $this->mysqlVersion(),
                'threads_connected' => $this->statusInt($status, 'Threads_connected'),
                'threads_running' => $threadsRunning,
                'slow_queries' => $this->statusInt($status, 'Slow_queries'),
                'questions' => $questions,
                'uptime_sec' => $uptime,
                'qps' => ($uptime && $uptime > 0 && $questions !== null)
                    ? round($questions / $uptime, 2)
                    : null,
                'tmp_disk_tables' => $this->statusInt($status, 'Created_tmp_disk_tables'),
                'max_used_connections' => $this->statusInt($status, 'Max_used_connections'),
                'running_queries' => $running,
                'top_statements' => $top,
                'query_logs' => $queryLogs,
                'warnings' => $warnings,
                'notes' => $notes,
            ];
        } catch (Throwable $e) {
            $empty['error'] = $e->getMessage();

            return $empty;
        }
    }

    private function mysqlStatusMap(): array
    {
        $rows = DB::select("SHOW GLOBAL STATUS WHERE Variable_name IN (
            'Threads_connected','Threads_running','Slow_queries','Questions','Uptime',
            'Created_tmp_disk_tables','Max_used_connections','Queries'
        )");

        $map = [];
        foreach ($rows as $row) {
            $name = (string) $this->rowValue($row, 'Variable_name');
            $map[$name] = $this->rowValue($row, 'Value');
        }

        return $map;
    }

    private function mysqlVersion(): ?string
    {
        try {
            $row = DB::selectOne('SELECT VERSION() AS v');

            return $row ? (string) $this->rowValue($row, 'v') : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    private function runningQueries(): array
    {
        try {
            $rows = DB::select(
                "SELECT ID, USER, HOST, DB, COMMAND, TIME, STATE, INFO
                 FROM information_schema.PROCESSLIST
                 WHERE COMMAND != 'Sleep'
                   AND INFO IS NOT NULL
                   AND INFO NOT LIKE '%information_schema.PROCESSLIST%'
                   AND INFO NOT LIKE '%events_statements_summary_by_digest%'
                 ORDER BY TIME DESC
                 LIMIT 25"
            );
        } catch (Throwable $e) {
            try {
                $rows = DB::select('SHOW FULL PROCESSLIST');
            } catch (Throwable $inner) {
                return [];
            }
        }

        $out = [];
        foreach ($rows as $row) {
            $command = (string) $this->rowValue($row, 'COMMAND');
            if (strcasecmp($command, 'Sleep') === 0) {
                continue;
            }
            $sql = $this->truncateSql((string) ($this->rowValue($row, 'INFO') ?? ''));
            if ($sql === '') {
                continue;
            }
            if (stripos($sql, 'events_statements_summary_by_digest') !== false
                || stripos($sql, 'information_schema.PROCESSLIST') !== false) {
                continue;
            }

            $out[] = [
                'id' => $this->rowValue($row, 'ID'),
                'user' => $this->rowValue($row, 'USER'),
                'host' => $this->rowValue($row, 'HOST'),
                'db' => $this->rowValue($row, 'DB'),
                'command' => $command,
                'time_sec' => (int) $this->rowValue($row, 'TIME'),
                'state' => $this->rowValue($row, 'STATE'),
                'sql' => $sql,
            ];
        }

        usort($out, fn ($a, $b) => ($b['time_sec'] <=> $a['time_sec']));

        return array_slice($out, 0, 20);
    }

    private function topStatements(array &$notes): array
    {
        $sql = "SELECT
                    DIGEST_TEXT AS query_sql,
                    COUNT_STAR AS exec_count,
                    ROUND(SUM_TIMER_WAIT / 1000000000000, 3) AS total_sec,
                    ROUND(AVG_TIMER_WAIT / 1000000000000, 3) AS avg_sec,
                    ROUND(MAX_TIMER_WAIT / 1000000000000, 3) AS max_sec,
                    SUM_ROWS_EXAMINED AS rows_examined,
                    SUM_ROWS_SENT AS rows_sent,
                    SUM_NO_INDEX_USED AS no_index,
                    LAST_SEEN AS last_seen
                FROM performance_schema.events_statements_summary_by_digest
                WHERE DIGEST_TEXT IS NOT NULL
                  AND DIGEST_TEXT NOT LIKE '%events_statements_summary_by_digest%'
                ORDER BY SUM_TIMER_WAIT DESC
                LIMIT 20";

        try {
            $rows = DB::select($sql);
        } catch (Throwable $e) {
            $notes[] = 'performance_schema top-query stats are not available on this server.';

            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'sql' => $this->truncateSql((string) $this->rowValue($row, 'query_sql')),
                'exec_count' => (int) $this->rowValue($row, 'exec_count'),
                'total_sec' => (float) $this->rowValue($row, 'total_sec'),
                'avg_sec' => (float) $this->rowValue($row, 'avg_sec'),
                'max_sec' => (float) $this->rowValue($row, 'max_sec'),
                'rows_examined' => (int) $this->rowValue($row, 'rows_examined'),
                'rows_sent' => (int) $this->rowValue($row, 'rows_sent'),
                'no_index' => (int) $this->rowValue($row, 'no_index'),
                'last_seen' => (string) $this->rowValue($row, 'last_seen'),
            ];
        }

        return $out;
    }

    private function hostCpuPercent(): ?float
    {
        if (! is_readable('/proc/stat')) {
            return null;
        }

        $current = $this->readProcStat();
        if ($current === null) {
            return null;
        }

        $previous = Cache::get('server_insights.cpu_stat');
        Cache::put('server_insights.cpu_stat', $current, 180);

        if (! is_array($previous) || ($current['total'] - ($previous['total'] ?? 0)) <= 0) {
            return null;
        }

        $idleDelta = $current['idle'] - (float) $previous['idle'];
        $totalDelta = $current['total'] - (float) $previous['total'];
        if ($totalDelta <= 0) {
            return null;
        }

        return round(max(0, min(100, (1 - ($idleDelta / $totalDelta)) * 100)), 1);
    }

    private function readProcStat(): ?array
    {
        $line = @file('/proc/stat', FILE_IGNORE_NEW_LINES);
        if (! is_array($line) || $line === []) {
            return null;
        }

        $parts = preg_split('/\s+/', trim($line[0]));
        if (! is_array($parts) || count($parts) < 5 || $parts[0] !== 'cpu') {
            return null;
        }

        $values = array_map('floatval', array_slice($parts, 1));
        $idle = ($values[3] ?? 0) + ($values[4] ?? 0);

        return [
            'idle' => $idle,
            'total' => array_sum($values),
        ];
    }

    private function cpuCores(): int
    {
        $n = 0;
        if (is_readable('/proc/cpuinfo')) {
            $cpuinfo = @file_get_contents('/proc/cpuinfo');
            if (is_string($cpuinfo)) {
                $n = substr_count($cpuinfo, 'processor');
            }
        }
        if ($n < 1 && function_exists('shell_exec')) {
            $out = @shell_exec('getconf _NPROCESSORS_ONLN 2>/dev/null');
            $n = (int) trim((string) $out);
        }

        return $n > 0 ? $n : 0;
    }

    private function hostMemory(): array
    {
        $empty = ['total_mb' => null, 'used_mb' => null, 'used_percent' => null];
        if (! is_readable('/proc/meminfo')) {
            return $empty;
        }

        $raw = @file_get_contents('/proc/meminfo');
        if (! is_string($raw) || $raw === '') {
            return $empty;
        }

        $kb = [];
        foreach (explode("\n", $raw) as $line) {
            if (preg_match('/^(\w+):\s+(\d+)/', $line, $m)) {
                $kb[$m[1]] = (int) $m[2];
            }
        }

        $total = $kb['MemTotal'] ?? 0;
        if ($total <= 0) {
            return $empty;
        }

        $available = $kb['MemAvailable'] ?? (($kb['MemFree'] ?? 0) + ($kb['Buffers'] ?? 0) + ($kb['Cached'] ?? 0));
        $used = max(0, $total - $available);

        return [
            'total_mb' => (int) round($total / 1024),
            'used_mb' => (int) round($used / 1024),
            'used_percent' => round(($used / $total) * 100, 1),
        ];
    }

    private function statusInt(array $status, string $key): ?int
    {
        if (! array_key_exists($key, $status) || $status[$key] === null) {
            return null;
        }

        return (int) $status[$key];
    }

    private function rowValue(object $row, string $key)
    {
        $arr = (array) $row;
        foreach ($arr as $k => $v) {
            if (strcasecmp((string) $k, $key) === 0) {
                return $v;
            }
        }

        return null;
    }

    private function truncateSql(string $sql): string
    {
        $sql = trim(preg_replace('/\s+/', ' ', $sql) ?? $sql);
        if (strlen($sql) <= self::SQL_LIMIT) {
            return $sql;
        }

        return substr($sql, 0, self::SQL_LIMIT) . '…';
    }
}
