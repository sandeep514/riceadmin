<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class DatabaseBackupController extends Controller
{
    /**
     * Stream a SQL dump of the default database (MySQL via mysqldump, or SQLite file download).
     */
    public function download()
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (($config['driver'] ?? '') === 'sqlite') {
            return $this->downloadSqlite($config);
        }

        if (($config['driver'] ?? '') !== 'mysql') {
            Session::flash('error', 'Error|Database backup is only supported for MySQL and SQLite.');

            return redirect()->route('home');
        }

        $finder = new ExecutableFinder();
        $mysqldump = $finder->find('mysqldump', null, ['/usr/bin', '/usr/local/bin', '/opt/homebrew/bin']);
        if ($mysqldump === null) {
            Session::flash('error', 'Error|mysqldump was not found. Install the MySQL client tools on the server and ensure mysqldump is on PATH.');

            return redirect()->route('home');
        }

        $host = $config['host'] ?? '127.0.0.1';
        $port = (string) ($config['port'] ?? '3306');
        $database = $config['database'] ?? '';
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';
        $socket = $config['unix_socket'] ?? '';

        $defaultsFile = $this->writeMysqlDefaultsFile($host, $port, $username, $password, $socket);
        if ($defaultsFile === null) {
            Session::flash('error', 'Error|Could not create temporary credentials file for backup.');

            return redirect()->route('home');
        }

        try {
            $args = [
                $mysqldump,
                '--defaults-extra-file=' . $defaultsFile,
                '--single-transaction',
                '--quick',
                '--routines',
                '--events',
                '--skip-comments',
                '--default-character-set=utf8mb4',
                $database,
            ];

            $process = new Process($args);
            $process->setTimeout(3600);
            $process->run();

            if (! $process->isSuccessful()) {
                Session::flash('error', 'Error|Backup failed: ' . trim($process->getErrorOutput() ?: $process->getOutput()));

                return redirect()->route('home');
            }

            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $database);
            $filename = 'db_backup_' . $safeName . '_' . date('Y-m-d_His') . '.sql';

            return response($process->getOutput(), 200, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } finally {
            if (is_string($defaultsFile) && is_file($defaultsFile)) {
                @unlink($defaultsFile);
            }
        }
    }

    private function downloadSqlite(array $config)
    {
        $path = $config['database'] ?? '';
        if ($path === '' || ! is_file($path)) {
            Session::flash('error', 'Error|SQLite database file not found.');

            return redirect()->route('home');
        }

        $filename = 'db_backup_' . date('Y-m-d_His') . '.sqlite';

        return response()->download($path, $filename);
    }

    /**
     * @return string|null Path to temp file
     */
    private function writeMysqlDefaultsFile(string $host, string $port, string $username, string $password, string $socket): ?string
    {
        $path = tempnam(sys_get_temp_dir(), 'mysqldump_');
        if ($path === false) {
            return null;
        }

        chmod($path, 0600);

        $lines = ["[client]"];
        if ($socket !== '') {
            $lines[] = 'socket=' . $socket;
        } else {
            $lines[] = 'host=' . $host;
            $lines[] = 'port=' . $port;
        }
        $lines[] = 'user=' . $username;
        $lines[] = 'password="' . str_replace(['\\', '"'], ['\\\\', '\\"'], $password) . '"';

        if (file_put_contents($path, implode("\n", $lines) . "\n") === false) {
            @unlink($path);

            return null;
        }

        return $path;
    }
}
