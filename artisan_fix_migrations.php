<?php
/**
 * Artisan command to fix migrations table
 * Usage: php artisan fix:migrations
 * 
 * Add this to app/Console/Kernel.php commands array, or run directly
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Simple direct fix
try {
    if (Schema::hasTable('migrations')) {
        echo "Resetting migrations table...\n";
        DB::statement('TRUNCATE TABLE migrations');
        DB::statement('ALTER TABLE migrations AUTO_INCREMENT = 1');
        echo "✓ Migrations table reset successfully!\n";
        echo "You can now run: php artisan migrate\n";
    } else {
        echo "Migrations table does not exist. Run: php artisan migrate:install\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

