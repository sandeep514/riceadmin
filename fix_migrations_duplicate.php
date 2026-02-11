<?php
/**
 * Fix duplicate entry error in migrations table
 * Run: php fix_migrations_duplicate.php
 */

// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Checking migrations table...\n";

try {
    if (!Schema::hasTable('migrations')) {
        echo "Migrations table does not exist. Creating it...\n";
        DB::statement("CREATE TABLE IF NOT EXISTS `migrations` (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `batch` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        echo "✓ Migrations table created.\n";
    } else {
        echo "Migrations table exists. Checking for duplicates...\n";
        
        // Check for duplicates
        $duplicates = DB::select("
            SELECT migration, COUNT(*) as count 
            FROM migrations 
            GROUP BY migration 
            HAVING count > 1
        ");
        
        if (count($duplicates) > 0) {
            echo "Found duplicate entries. Fixing...\n";
            
            // Remove duplicates, keeping the one with the lowest id
            DB::statement("
                DELETE m1 FROM migrations m1
                INNER JOIN migrations m2 
                WHERE m1.id > m2.id AND m1.migration = m2.migration
            ");
            
            echo "✓ Removed duplicate entries.\n";
        }
        
        // Reset auto-increment
        $maxId = DB::selectOne("SELECT MAX(id) as max_id FROM migrations");
        $nextId = ($maxId->max_id ?? 0) + 1;
        DB::statement("ALTER TABLE migrations AUTO_INCREMENT = $nextId");
        
        echo "✓ Auto-increment reset to $nextId.\n";
    }
    
    echo "\n✓ Migrations table is ready!\n";
    echo "You can now run: php artisan migrate\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nTrying alternative method (truncate table)...\n";
    
    try {
        DB::statement('TRUNCATE TABLE migrations');
        DB::statement('ALTER TABLE migrations AUTO_INCREMENT = 1');
        echo "✓ Migrations table truncated and reset.\n";
        echo "You can now run: php artisan migrate\n";
    } catch (Exception $e2) {
        echo "✗ Error: " . $e2->getMessage() . "\n";
        echo "\nPlease check your database connection in .env file.\n";
    }
}

