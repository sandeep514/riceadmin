<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddLivePricesCreatedAtFirstIndex extends Migration
{
    public function up()
    {
        // created_at first so day-range filters can range-scan, then apply name/other WHERE.
        $this->addIndexIfMissing(
            'live_prices',
            'idx_live_prices_created_at_name',
            ['created_at', 'name', 'state_order']
        );
    }

    public function down()
    {
        $this->dropIndexIfExists('live_prices', 'idx_live_prices_created_at_name');
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
