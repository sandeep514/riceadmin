<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * paddy_prices.created_at carries the admin-chosen PRICE DATE (always IST
 * midnight), not a true instant. As MySQL TIMESTAMP it is stored in UTC and
 * converted to the reader's session time_zone on every read — so a raw-DB /
 * phpMyAdmin viewer on UTC sees 2026-07-02 00:00 +05:30 as 2026-07-01 18:30
 * ("one date back"), while the portal (session +05:30) shows July 2.
 *
 * Fix: store both columns as DATETIME (literal wall time, no conversion) and
 * backfill existing rows from UTC to IST wall time, so every client sees the
 * true price date. Lookup indexes on created_at are rebuilt after the swap.
 *
 * Run with a table backup first. Offset-based CONVERT_TZ needs no tz tables.
 */
class ConvertPaddyPricesTimestampsToDatetime extends Migration
{
    public function up()
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("SET time_zone = '+00:00'");

        $this->dropIndexIfExists('paddy_prices', 'idx_paddy_prices_created_at');
        $this->dropIndexIfExists('paddy_prices', 'idx_paddy_prices_state_mandi_crop_created');

        DB::statement('ALTER TABLE `paddy_prices` ADD COLUMN `created_at_new` DATETIME NULL AFTER `status`');
        DB::statement('ALTER TABLE `paddy_prices` ADD COLUMN `updated_at_new` DATETIME NULL AFTER `created_at_new`');

        // Session is UTC: reading TIMESTAMP yields UTC wall time, which we
        // shift to IST wall time for literal DATETIME storage.
        DB::statement("UPDATE `paddy_prices` SET `created_at_new` = CONVERT_TZ(`created_at`, '+00:00', '+05:30')");
        DB::statement("UPDATE `paddy_prices` SET `updated_at_new` = CONVERT_TZ(`updated_at`, '+00:00', '+05:30')");

        DB::statement('ALTER TABLE `paddy_prices` DROP COLUMN `created_at`, DROP COLUMN `updated_at`');
        DB::statement('ALTER TABLE `paddy_prices` CHANGE COLUMN `created_at_new` `created_at` DATETIME NULL');
        DB::statement('ALTER TABLE `paddy_prices` CHANGE COLUMN `updated_at_new` `updated_at` DATETIME NULL');

        $this->addIndexIfMissing(
            'paddy_prices',
            'idx_paddy_prices_state_mandi_crop_created',
            Schema::hasColumn('paddy_prices', 'crop_year')
                ? ['state', 'mandi', 'crop_year', 'created_at']
                : ['state', 'mandi', 'created_at']
        );
        $this->addIndexIfMissing('paddy_prices', 'idx_paddy_prices_created_at', ['created_at']);

        DB::statement("SET time_zone = '+05:30'");
    }

    public function down()
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("SET time_zone = '+00:00'");

        $this->dropIndexIfExists('paddy_prices', 'idx_paddy_prices_created_at');
        $this->dropIndexIfExists('paddy_prices', 'idx_paddy_prices_state_mandi_crop_created');

        DB::statement('ALTER TABLE `paddy_prices` ADD COLUMN `created_at_old` TIMESTAMP NULL AFTER `status`');
        DB::statement('ALTER TABLE `paddy_prices` ADD COLUMN `updated_at_old` TIMESTAMP NULL AFTER `created_at_old`');

        // DATETIME holds IST wall time; shift back to UTC wall time, which the
        // UTC session then stores as the same instant in TIMESTAMP columns.
        DB::statement("UPDATE `paddy_prices` SET `created_at_old` = CONVERT_TZ(`created_at`, '+05:30', '+00:00')");
        DB::statement("UPDATE `paddy_prices` SET `updated_at_old` = CONVERT_TZ(`updated_at`, '+05:30', '+00:00')");

        DB::statement('ALTER TABLE `paddy_prices` DROP COLUMN `created_at`, DROP COLUMN `updated_at`');
        DB::statement('ALTER TABLE `paddy_prices` CHANGE COLUMN `created_at_old` `created_at` TIMESTAMP NULL');
        DB::statement('ALTER TABLE `paddy_prices` CHANGE COLUMN `updated_at_old` `updated_at` TIMESTAMP NULL');

        $this->addIndexIfMissing(
            'paddy_prices',
            'idx_paddy_prices_state_mandi_crop_created',
            Schema::hasColumn('paddy_prices', 'crop_year')
                ? ['state', 'mandi', 'crop_year', 'created_at']
                : ['state', 'mandi', 'created_at']
        );
        $this->addIndexIfMissing('paddy_prices', 'idx_paddy_prices_created_at', ['created_at']);

        DB::statement("SET time_zone = '+05:30'");
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
        return count(DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$index])) > 0;
    }
}
