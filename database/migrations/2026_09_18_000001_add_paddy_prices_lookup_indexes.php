<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPaddyPricesLookupIndexes extends Migration
{
    public function up()
    {
        $lookupColumns = Schema::hasColumn('paddy_prices', 'crop_year')
            ? ['state', 'mandi', 'crop_year', 'created_at']
            : ['state', 'mandi', 'created_at'];

        $this->addIndexIfMissing(
            'paddy_prices',
            'idx_paddy_prices_state_mandi_crop_created',
            $lookupColumns
        );
        $this->addIndexIfMissing(
            'paddy_prices',
            'idx_paddy_prices_created_at',
            ['created_at']
        );
    }

    public function down()
    {
        $this->dropIndexIfExists('paddy_prices', 'idx_paddy_prices_created_at');
        $this->dropIndexIfExists('paddy_prices', 'idx_paddy_prices_state_mandi_crop_created');
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
