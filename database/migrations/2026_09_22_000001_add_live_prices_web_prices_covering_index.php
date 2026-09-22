<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddLivePricesWebPricesCoveringIndex extends Migration
{
    public function up()
    {
        // Covering index for the web/app price endpoints' day-slice lookups:
        // (state, cropYear, created_at) range + name/form filter + MAX(id),
        // answered from the index alone (no table touch).
        $this->addIndexIfMissing(
            'live_prices',
            'idx_live_prices_state_crop_created_cover',
            ['state', 'cropYear', 'created_at', 'name', 'form', 'id']
        );

        // Latest-row probe per state+year (ORDER BY id DESC LIMIT 1 fallbacks).
        $this->addIndexIfMissing(
            'live_prices',
            'idx_live_prices_state_crop_id',
            ['state', 'cropYear', 'id']
        );

        // Strict subsets of the covering index above (same leftmost prefix);
        // dropping forces the optimizer onto the covering index and
        // reduces write overhead. Kept idempotent for all environments.
        $this->dropIndexIfExists('live_prices', 'idx_live_prices_state_crop_created');
        $this->dropIndexIfExists('live_prices', 'idx_state_crop_created');
    }

    public function down()
    {
        $this->dropIndexIfExists('live_prices', 'idx_live_prices_state_crop_id');
        $this->dropIndexIfExists('live_prices', 'idx_live_prices_state_crop_created_cover');
        $this->addIndexIfMissing(
            'live_prices',
            'idx_live_prices_state_crop_created',
            ['state', 'cropYear', 'created_at']
        );
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
        $rows = DB::select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]);

        return count($rows) > 0;
    }
}
