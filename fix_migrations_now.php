<?php
/**
 * Fix duplicate entry error in migrations table
 * Run: php fix_migrations_now.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Fixing Migrations Table Duplicate Entry Error ===\n\n";

try {
    // Check if migrations table exists
    if (!Schema::hasTable('migrations')) {
        echo "Creating migrations table...\n";
        DB::statement("
            CREATE TABLE `migrations` (
                `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `batch` int(11) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✓ Migrations table created.\n\n";
    }
    
    // Get current state
    $currentCount = DB::table('migrations')->count();
    echo "Current migrations count: $currentCount\n";
    
    // Check for duplicates
    $duplicates = DB::select("
        SELECT migration, COUNT(*) as count 
        FROM migrations 
        GROUP BY migration 
        HAVING count > 1
    ");
    
    if (count($duplicates) > 0) {
        echo "Found " . count($duplicates) . " duplicate migration(s).\n";
        foreach ($duplicates as $dup) {
            echo "  - {$dup->migration} appears {$dup->count} times\n";
        }
        echo "\nRemoving duplicates...\n";
        
        // Remove duplicates, keeping the one with the lowest id
        DB::statement("
            DELETE m1 FROM migrations m1
            INNER JOIN migrations m2 
            WHERE m1.id > m2.id AND m1.migration = m2.migration
        ");
        
        echo "✓ Duplicates removed.\n";
    }
    
    // Fix auto-increment
    echo "\nFixing auto-increment...\n";
    $maxId = DB::selectOne("SELECT COALESCE(MAX(id), 0) as max_id FROM migrations");
    $nextId = $maxId->max_id + 1;
    
    DB::statement("ALTER TABLE migrations AUTO_INCREMENT = $nextId");
    echo "✓ Auto-increment set to $nextId.\n";
    
    // Verify
    $finalCount = DB::table('migrations')->count();
    echo "\nFinal migrations count: $finalCount\n";
    
    echo "\n=== ✓ Migrations table fixed successfully! ===\n";
    echo "You can now run: php artisan migrate\n";
    
} catch (Exception $e) {
    echo "\n✗ Error occurred: " . $e->getMessage() . "\n";
    echo "\nTrying alternative method (truncate and reset)...\n";
    
    try {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('TRUNCATE TABLE migrations');
        DB::statement('ALTER TABLE migrations AUTO_INCREMENT = 1');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        echo "✓ Migrations table truncated and reset.\n";
        echo "You can now run: php artisan migrate\n";
    } catch (Exception $e2) {
        echo "✗ Error: " . $e2->getMessage() . "\n";
        echo "\nPlease check:\n";
        echo "1. Database connection in .env file\n";
        echo "2. Database user has proper permissions\n";
        echo "3. Database exists\n";
    }
}

