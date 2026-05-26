<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddLivePriceLookupIndexes extends Migration
{
    public function up()
    {
        $this->addIndexIfMissing('live_prices', 'idx_live_prices_state_crop_created', ['state', 'cropYear', 'created_at']);
        $this->addIndexIfMissing('live_prices', 'idx_live_prices_tuple_latest', ['state', 'cropYear', 'name', 'form', 'id']);
        $this->addIndexIfMissing('live_prices', 'idx_live_prices_crop_updated', ['cropYear', 'updated_at']);
        $this->addIndexIfMissing('live_price_closing', 'idx_live_price_closing_lookup', ['state', 'cropYear', 'name', 'form', 'id']);
    }

    public function down()
    {
        $this->dropIndexIfExists('live_price_closing', 'idx_live_price_closing_lookup');
        $this->dropIndexIfExists('live_prices', 'idx_live_prices_crop_updated');
        $this->dropIndexIfExists('live_prices', 'idx_live_prices_tuple_latest');
        $this->dropIndexIfExists('live_prices', 'idx_live_prices_state_crop_created');
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
