<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Speeds global MAX(updated_at) / latest-edit probes on live_prices
 * (used by price APIs and lastUpdatedDate formatting).
 */
class AddLivePricesUpdatedAtIndex extends Migration
{
    public function up()
    {
        $this->addIndexIfMissing('live_prices', 'idx_live_prices_updated_at', ['updated_at']);
    }

    public function down()
    {
        $this->dropIndexIfExists('live_prices', 'idx_live_prices_updated_at');
    }

    private function addIndexIfMissing(string $table, string $index, array $columns): void
    {
        if ($this->indexExists($table, $index)) {
            return;
        }

        DB::statement(sprintf(
            'ALTER TABLE `%s` ADD INDEX `%s` (`%s`)',
            $table,
            $index,
            implode('`, `', $columns)
        ));
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (! $this->indexExists($table, $index)) {
            return;
        }

        DB::statement(sprintf('ALTER TABLE `%s` DROP INDEX `%s`', $table, $index));
    }

    private function indexExists(string $table, string $index): bool
    {
        $rows = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$index]);

        return count($rows) > 0;
    }
}
