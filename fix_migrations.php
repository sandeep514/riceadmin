<?php
/**
 * Quick script to fix duplicate entries in migrations table
 * Run: php fix_migrations.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Fixing migrations table...\n";
    
    // Option 1: Clear all and reset
    DB::statement('TRUNCATE TABLE migrations');
    DB::statement('ALTER TABLE migrations AUTO_INCREMENT = 1');
    
    echo "Migrations table has been reset.\n";
    echo "You can now run: php artisan migrate\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Make sure your database connection is configured correctly in .env\n";
}

