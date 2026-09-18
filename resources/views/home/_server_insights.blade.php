@php
    $host = $serverInsights['host'] ?? [];
    $mysql = $serverInsights['mysql'] ?? [];
    $cpu = $host['cpu_percent'] ?? null;
    $load1 = $host['load_1'] ?? null;
    $memPct = $host['memory']['used_percent'] ?? null;
    $threadsRunning = $mysql['threads_running'] ?? null;
    $cpuBox = ($cpu !== null && $cpu >= 85) ? 'bg-red' : (($cpu !== null && $cpu >= 60) ? 'bg-yellow' : 'bg-green');
    $memBox = ($memPct !== null && $memPct >= 90) ? 'bg-red' : 'bg-aqua';
    $threadBox = ($threadsRunning !== null && $threadsRunning >= 8) ? 'bg-red' : 'bg-yellow';
    $warnings = array_merge($host['warnings'] ?? [], $mysql['warnings'] ?? []);
@endphp

<div id="server-insights" data-url="{{ route('server.insights') }}">
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="small-box {{ $cpuBox }}" id="si-cpu-box">
                <div class="inner">
                    <h3 id="si-cpu">{{ $cpu !== null ? $cpu.'%' : ($load1 !== null ? $load1 : '—') }}</h3>
                    <p>CPU {{ $cpu !== null ? 'usage' : 'load (1m)' }}</p>
                </div>
                <div class="icon"><i class="fa fa-microchip"></i></div>
                <span class="small-box-footer">
                    Cores: <span id="si-cores">{{ $host['cpu_cores'] ?: '—' }}</span>
                    &nbsp;|&nbsp; Load 1/5/15:
                    <span id="si-load">{{ implode(' / ', array_filter([$host['load_1'] ?? null, $host['load_5'] ?? null, $host['load_15'] ?? null], fn ($v) => $v !== null)) ?: '—' }}</span>
                </span>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box {{ $memBox }}" id="si-mem-box">
                <div class="inner">
                    <h3 id="si-mem">{{ $memPct !== null ? $memPct.'%' : '—' }}</h3>
                    <p>Memory used</p>
                </div>
                <div class="icon"><i class="fa fa-database"></i></div>
                <span class="small-box-footer" id="si-mem-detail">
                    {{ ($host['memory']['used_mb'] ?? null) !== null ? $host['memory']['used_mb'].' / '.$host['memory']['total_mb'].' MB' : 'Host memory not available on this OS' }}
                </span>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box {{ $threadBox }}" id="si-thread-box">
                <div class="inner">
                    <h3 id="si-threads">{{ $threadsRunning ?? '—' }}</h3>
                    <p>MySQL threads running</p>
                </div>
                <div class="icon"><i class="fa fa-cogs"></i></div>
                <span class="small-box-footer">
                    Connected: <span id="si-connected">{{ $mysql['threads_connected'] ?? '—' }}</span>
                    &nbsp;|&nbsp; QPS: <span id="si-qps">{{ $mysql['qps'] ?? '—' }}</span>
                </span>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3 id="si-slow">{{ $mysql['slow_queries'] ?? '—' }}</h3>
                    <p>Slow queries (since uptime)</p>
                </div>
                <div class="icon"><i class="fa fa-hourglass-half"></i></div>
                <span class="small-box-footer">
                    Disk temp tables: <span id="si-tmp">{{ $mysql['tmp_disk_tables'] ?? '—' }}</span>
                </span>
            </div>
        </div>
    </div>

    <div id="si-warnings">
        @foreach($warnings as $warning)
            <div class="alert alert-danger" style="margin-bottom:10px;">{{ $warning }}</div>
        @endforeach
        @if(!empty($mysql['error']))
            <div class="alert alert-warning">MySQL metrics error: {{ $mysql['error'] }}</div>
        @endif
    </div>

    <div class="box box-danger">
        <div class="box-header with-border">
            <h3 class="box-title">Currently running SQL (longest first)</h3>
            <div class="box-tools pull-right">
                <span class="text-muted" id="si-collected">Updated {{ $serverInsights['collected_at'] ?? '' }}</span>
                &nbsp;
                <button type="button" class="btn btn-box-tool" id="si-refresh-btn" title="Refresh now">
                    <i class="fa fa-refresh"></i>
                </button>
            </div>
        </div>
        <div class="box-body table-responsive no-padding">
            <table class="table table-striped table-hover" style="margin-bottom:0;">
                <thead>
                    <tr>
                        <th>Time (s)</th>
                        <th>Id</th>
                        <th>User</th>
                        <th>DB</th>
                        <th>State</th>
                        <th>Query</th>
                    </tr>
                </thead>
                <tbody id="si-running-body">
                    @forelse(($mysql['running_queries'] ?? []) as $q)
                        <tr class="{{ ((int) $q['time_sec'] >= 5) ? 'danger' : '' }}">
                            <td>{{ $q['time_sec'] }}</td>
                            <td>{{ $q['id'] }}</td>
                            <td>{{ $q['user'] }}{{ '@' }}{{ $q['host'] }}</td>
                            <td>{{ $q['db'] ?: '—' }}</td>
                            <td>{{ $q['state'] ?: '—' }}</td>
                            <td style="white-space:pre-wrap; max-width:720px; font-family:monospace; font-size:12px;">{{ $q['sql'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No active queries right now.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">SQL using the most time (likely CPU) since MySQL start</h3>
        </div>
        <div class="box-body table-responsive no-padding">
            <table class="table table-striped table-hover" style="margin-bottom:0;">
                <thead>
                    <tr>
                        <th>Total sec</th>
                        <th>Avg / Max</th>
                        <th>Executions</th>
                        <th>Rows examined</th>
                        <th>No index</th>
                        <th>Last seen</th>
                        <th>Query</th>
                    </tr>
                </thead>
                <tbody id="si-top-body">
                    @forelse(($mysql['top_statements'] ?? []) as $q)
                        <tr class="{{ ($q['avg_sec'] >= 1 || $q['no_index'] > 0) ? 'warning' : '' }}">
                            <td>{{ $q['total_sec'] }}</td>
                            <td>{{ $q['avg_sec'] }} / {{ $q['max_sec'] }}</td>
                            <td>{{ number_format($q['exec_count']) }}</td>
                            <td>{{ number_format($q['rows_examined']) }}</td>
                            <td>{{ number_format($q['no_index']) }}</td>
                            <td>{{ $q['last_seen'] }}</td>
                            <td style="white-space:pre-wrap; max-width:640px; font-family:monospace; font-size:12px;">{{ $q['sql'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No statement digest data. Enable performance_schema on MySQL.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="box-footer">
            <small class="text-muted" id="si-notes">
                {{ implode(' ', $mysql['notes'] ?? []) }}
                Host: {{ $host['hostname'] ?? '' }} · {{ $mysql['version'] ?? '' }}
            </small>
        </div>
    </div>
</div>

<script>
(function () {
    var root = document.getElementById('server-insights');
    if (!root) return;
    var url = root.getAttribute('data-url');
    var timer = null;

    function esc(v) {
        return String(v == null ? '' : v)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function fmt(n) {
        if (n == null || n === '') return '—';
        return Number(n).toLocaleString();
    }

    function setBox(id, cls) {
        var el = document.getElementById(id);
        if (!el) return;
        el.className = 'small-box ' + cls;
    }

    function render(data) {
        var host = data.host || {};
        var mysql = data.mysql || {};
        var cpu = host.cpu_percent;
        var load = [host.load_1, host.load_5, host.load_15].filter(function (v) { return v != null; }).join(' / ') || '—';
        var mem = host.memory || {};
        var threads = mysql.threads_running;

        document.getElementById('si-cpu').textContent = cpu != null ? (cpu + '%') : (host.load_1 != null ? host.load_1 : '—');
        document.getElementById('si-cores').textContent = host.cpu_cores || '—';
        document.getElementById('si-load').textContent = load;
        document.getElementById('si-mem').textContent = mem.used_percent != null ? (mem.used_percent + '%') : '—';
        document.getElementById('si-mem-detail').textContent = mem.used_mb != null
            ? (mem.used_mb + ' / ' + mem.total_mb + ' MB')
            : 'Host memory not available on this OS';
        document.getElementById('si-threads').textContent = threads != null ? threads : '—';
        document.getElementById('si-connected').textContent = mysql.threads_connected != null ? mysql.threads_connected : '—';
        document.getElementById('si-qps').textContent = mysql.qps != null ? mysql.qps : '—';
        document.getElementById('si-slow').textContent = mysql.slow_queries != null ? mysql.slow_queries : '—';
        document.getElementById('si-tmp').textContent = mysql.tmp_disk_tables != null ? mysql.tmp_disk_tables : '—';
        document.getElementById('si-collected').textContent = 'Updated ' + (data.collected_at || '');

        setBox('si-cpu-box', cpu >= 85 ? 'bg-red' : (cpu >= 60 ? 'bg-yellow' : 'bg-green'));
        setBox('si-mem-box', mem.used_percent >= 90 ? 'bg-red' : 'bg-aqua');
        setBox('si-thread-box', threads >= 8 ? 'bg-red' : 'bg-yellow');

        var warnings = [].concat(host.warnings || [], mysql.warnings || []);
        var warnHtml = warnings.map(function (w) {
            return '<div class="alert alert-danger" style="margin-bottom:10px;">' + esc(w) + '</div>';
        }).join('');
        if (mysql.error) {
            warnHtml += '<div class="alert alert-warning">MySQL metrics error: ' + esc(mysql.error) + '</div>';
        }
        document.getElementById('si-warnings').innerHTML = warnHtml;

        var running = mysql.running_queries || [];
        var runningBody = document.getElementById('si-running-body');
        if (!running.length) {
            runningBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No active queries right now.</td></tr>';
        } else {
            runningBody.innerHTML = running.map(function (q) {
                return '<tr class="' + (q.time_sec >= 5 ? 'danger' : '') + '">' +
                    '<td>' + esc(q.time_sec) + '</td>' +
                    '<td>' + esc(q.id) + '</td>' +
                    '<td>' + esc(q.user) + '@' + esc(q.host) + '</td>' +
                    '<td>' + esc(q.db || '—') + '</td>' +
                    '<td>' + esc(q.state || '—') + '</td>' +
                    '<td style="white-space:pre-wrap; max-width:720px; font-family:monospace; font-size:12px;">' + esc(q.sql) + '</td>' +
                    '</tr>';
            }).join('');
        }

        var top = mysql.top_statements || [];
        var topBody = document.getElementById('si-top-body');
        if (!top.length) {
            topBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No statement digest data. Enable performance_schema on MySQL.</td></tr>';
        } else {
            topBody.innerHTML = top.map(function (q) {
                return '<tr class="' + ((q.avg_sec >= 1 || q.no_index > 0) ? 'warning' : '') + '">' +
                    '<td>' + esc(q.total_sec) + '</td>' +
                    '<td>' + esc(q.avg_sec) + ' / ' + esc(q.max_sec) + '</td>' +
                    '<td>' + fmt(q.exec_count) + '</td>' +
                    '<td>' + fmt(q.rows_examined) + '</td>' +
                    '<td>' + fmt(q.no_index) + '</td>' +
                    '<td>' + esc(q.last_seen) + '</td>' +
                    '<td style="white-space:pre-wrap; max-width:640px; font-family:monospace; font-size:12px;">' + esc(q.sql) + '</td>' +
                    '</tr>';
            }).join('');
        }

        document.getElementById('si-notes').textContent = (mysql.notes || []).join(' ') +
            ' Host: ' + (host.hostname || '') + ' · ' + (mysql.version || '');

        var logs = mysql.query_logs || [];
        var logBody = document.getElementById('si-log-body');
        if (logBody) {
            if (!logs.length) {
                logBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No heavy SQL logged yet. Slow queries will appear here automatically.</td></tr>';
            } else {
                logBody.innerHTML = logs.map(function (log) {
                    return '<tr class="' + (log.duration_ms >= 2000 ? 'danger' : 'warning') + '">' +
                        '<td>' + esc(log.created_at) + '</td>' +
                        '<td>' + esc(log.duration_sec) + 's (' + fmt(log.duration_ms) + ' ms)</td>' +
                        '<td>' + (log.cpu_percent != null ? esc(log.cpu_percent) + '%' : '—') + '</td>' +
                        '<td>' + esc(log.source) + '</td>' +
                        '<td>' + esc(log.request_path || log.connection || '—') + '</td>' +
                        '<td style="white-space:pre-wrap; max-width:720px; font-family:monospace; font-size:12px;">' + esc(log.sql) + '</td>' +
                        '</tr>';
                }).join('');
            }
        }
    }

    function refresh() {
        fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (res) { return res.json(); })
            .then(function (json) {
                if (json && json.data) {
                    render(json.data);
                }
            })
            .catch(function () {});
    }

    document.getElementById('si-refresh-btn').addEventListener('click', refresh);
    timer = setInterval(refresh, 8000);
})();
</script>
