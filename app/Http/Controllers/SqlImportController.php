<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Session;

class SqlImportController extends Controller
{
    public function index()
    {
        $dbName = config('database.connections.mysql.database');
        return view('sql-import.index', compact('dbName'));
    }

    public function import(Request $request)
    {
        // Remove file size limit for large files
        $request->validate([
            'sql_file' => 'required|file|mimes:sql,txt',
            'database_name' => 'required|string|max:255',
        ]);

        // Increase PHP limits for large file processing
        ini_set('max_execution_time', 0); // No time limit
        ini_set('memory_limit', '2048M'); // 2GB memory limit
        
        try {
            $file = $request->file('sql_file');
            $databaseName = $request->database_name;
            $filePath = $file->getRealPath();
            
            // Get current database connection
            $currentDb = config('database.connections.mysql.database');
            
            // Switch to the specified database
            config(['database.connections.mysql.database' => $databaseName]);
            DB::purge('mysql');
            
            // Try using MySQL command line first (most efficient for large files)
            $useCommandLine = $this->importUsingCommandLine($filePath, $databaseName, $currentDb);
            
            if ($useCommandLine) {
                // Command line import was successful
                Session::flash('success', 'Success|SQL file imported successfully using MySQL command line tool!');
                return redirect()->route('sql-import');
            }
            
            // Fallback to PHP processing for large files (streaming)
            $this->importUsingStreaming($filePath, $databaseName, $currentDb);
            
        } catch (\Exception $e) {
            // Restore original database connection in case of error
            try {
                $currentDb = config('database.connections.mysql.database');
                if (isset($currentDb)) {
                    config(['database.connections.mysql.database' => $currentDb]);
                    DB::purge('mysql');
                }
            } catch (\Exception $ex) {
                // Ignore
            }
            
            Session::flash('error', 'Error|Failed to process SQL file: ' . $e->getMessage());
        }
        
        return redirect()->route('sql-import');
    }
    
    /**
     * Try to import using MySQL command line (most efficient for large files)
     */
    private function importUsingCommandLine($filePath, $databaseName, $currentDb)
    {
        try {
            $dbHost = config('database.connections.mysql.host');
            $dbPort = config('database.connections.mysql.port', '3306');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            
            // Check if mysql command is available
            $mysqlPath = $this->findMysqlCommand();
            
            if (!$mysqlPath) {
                return false;
            }
            
            // Build command
            $command = sprintf(
                '%s -h%s -P%s -u%s %s %s < %s 2>&1',
                escapeshellarg($mysqlPath),
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                $dbPass ? '-p' . escapeshellarg($dbPass) : '',
                escapeshellarg($databaseName),
                escapeshellarg($filePath)
            );
            
            // Execute command
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);
            
            if ($returnVar === 0) {
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Find MySQL command path
     */
    private function findMysqlCommand()
    {
        $possiblePaths = [
            '/usr/bin/mysql',
            '/usr/local/bin/mysql',
            '/opt/homebrew/bin/mysql',
            'mysql', // In PATH
        ];
        
        foreach ($possiblePaths as $path) {
            if (is_executable($path) || shell_exec("which $path")) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Import using streaming for large files
     */
    private function importUsingStreaming($filePath, $databaseName, $currentDb)
    {
        $executedCount = 0;
        $errors = [];
        $currentStatement = '';
        $lineNumber = 0;
        
        // Open file for reading
        $handle = fopen($filePath, 'r');
        
        if (!$handle) {
            throw new \Exception('Could not open SQL file for reading.');
        }
        
        DB::beginTransaction();
        
        try {
            while (($line = fgets($handle)) !== false) {
                $lineNumber++;
                $line = trim($line);
                
                // Skip empty lines and comments
                if (empty($line) || strpos($line, '--') === 0 || strpos($line, '/*') === 0) {
                    continue;
                }
                
                // Remove inline comments
                $line = preg_replace('/--.*$/', '', $line);
                
                $currentStatement .= $line . "\n";
                
                // Check if line ends with semicolon (end of statement)
                if (substr(rtrim($line), -1) === ';') {
                    $statement = trim($currentStatement);
                    $statement = rtrim($statement, ';');
                    
                    if (!empty($statement) && strlen($statement) > 3) {
                        try {
                            DB::statement($statement);
                            $executedCount++;
                            
                            // Commit in batches of 1000 statements to avoid memory issues
                            if ($executedCount % 1000 === 0) {
                                DB::commit();
                                DB::beginTransaction();
                            }
                        } catch (\Exception $e) {
                            $errors[] = [
                                'line' => $lineNumber,
                                'statement' => substr($statement, 0, 150) . (strlen($statement) > 150 ? '...' : ''),
                                'error' => $e->getMessage()
                            ];
                            
                            // Continue processing even if one statement fails
                        }
                    }
                    
                    $currentStatement = '';
                }
            }
            
            // Execute any remaining statement
            if (!empty(trim($currentStatement))) {
                $statement = trim($currentStatement);
                $statement = rtrim($statement, ';');
                
                if (!empty($statement) && strlen($statement) > 3) {
                    try {
                        DB::statement($statement);
                        $executedCount++;
                    } catch (\Exception $e) {
                        $errors[] = [
                            'line' => $lineNumber,
                            'statement' => substr($statement, 0, 150) . (strlen($statement) > 150 ? '...' : ''),
                            'error' => $e->getMessage()
                        ];
                    }
                }
            }
            
            fclose($handle);
            
            DB::commit();
            
            // Restore original database connection
            config(['database.connections.mysql.database' => $currentDb]);
            DB::purge('mysql');
            
            if (count($errors) > 0) {
                $errorDetails = '';
                foreach (array_slice($errors, 0, 5) as $error) {
                    $errorDetails .= 'Line ' . $error['line'] . ': ' . substr($error['error'], 0, 100) . '; ';
                }
                if (count($errors) > 5) {
                    $errorDetails .= '... and ' . (count($errors) - 5) . ' more errors.';
                }
                Session::flash('warning', 'Warning|SQL file imported with ' . count($errors) . ' error(s). ' . $executedCount . ' statement(s) executed successfully. ' . $errorDetails);
            } else {
                Session::flash('success', 'Success|SQL file imported successfully! ' . $executedCount . ' statement(s) executed.');
            }
            
        } catch (\Exception $e) {
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
            
            DB::rollBack();
            
            // Restore original database connection
            config(['database.connections.mysql.database' => $currentDb]);
            DB::purge('mysql');
            
            throw $e;
        }
    }
}

