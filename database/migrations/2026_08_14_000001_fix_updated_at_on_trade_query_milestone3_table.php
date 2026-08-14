<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixUpdatedAtOnTradeQueryMilestone3Table extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('trade_query_milestone3')) {
            return;
        }

        // Column was defined as integer; Laravel writes full datetimes so MySQL kept only the year (e.g. 2026).
        $column = collect(DB::select("SHOW COLUMNS FROM `trade_query_milestone3` WHERE Field = 'updated_at'"))->first();
        if (! $column) {
            Schema::table('trade_query_milestone3', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });

            return;
        }

        $type = strtolower((string) ($column->Type ?? ''));
        if (str_contains($type, 'int') || $type === 'year') {
            // Convert year-only values using created_at; keep real unix timestamps if any.
            DB::statement("
                UPDATE `trade_query_milestone3`
                SET `updated_at` = NULL
                WHERE `updated_at` IS NOT NULL
                  AND `updated_at` > 0
                  AND `updated_at` < 10000
            ");

            DB::statement("
                ALTER TABLE `trade_query_milestone3`
                MODIFY `updated_at` TIMESTAMP NULL DEFAULT NULL
            ");

            // Fill null/zero-like rows from created_at where possible
            DB::statement("
                UPDATE `trade_query_milestone3`
                SET `updated_at` = `created_at`
                WHERE `updated_at` IS NULL
                  AND `created_at` IS NOT NULL
            ");
        }
    }

    public function down()
    {
        if (! Schema::hasTable('trade_query_milestone3')) {
            return;
        }

        DB::statement("
            ALTER TABLE `trade_query_milestone3`
            MODIFY `updated_at` INT NULL
        ");
    }
}
