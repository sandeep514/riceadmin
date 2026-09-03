<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddLivePricesCropCreatedIndex extends Migration
{
    public function up()
    {
        $this->addIndexIfMissing(
            'live_prices',
            'idx_live_prices_crop_created_id',
            ['cropYear', 'created_at', 'id']
        );
    }

    public function down()
    {
        $this->dropIndexIfExists('live_prices', 'idx_live_prices_crop_created_id');
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
