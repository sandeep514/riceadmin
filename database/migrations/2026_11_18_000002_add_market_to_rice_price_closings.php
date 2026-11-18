<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Scope special closed days per market: live | rice_sourcing | paddy.
 * Existing rows become market=live.
 */
class AddMarketToRicePriceClosings extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('rice_price_closings')) {
            return;
        }

        Schema::table('rice_price_closings', function (Blueprint $table) {
            if (! Schema::hasColumn('rice_price_closings', 'market')) {
                $table->string('market', 32)->default('live')->after('id');
            }
        });

        DB::table('rice_price_closings')
            ->where(function ($q) {
                $q->whereNull('market')->orWhere('market', '');
            })
            ->update(['market' => 'live']);

        // Replace date-only unique with per-market unique (ignore if already migrated).
        try {
            Schema::table('rice_price_closings', function (Blueprint $table) {
                $table->dropUnique('rice_price_closings_close_date_unique');
            });
        } catch (\Throwable $e) {
            // Already dropped or differently named.
        }

        try {
            Schema::table('rice_price_closings', function (Blueprint $table) {
                $table->unique(['market', 'close_date'], 'rice_price_closings_market_close_date_unique');
            });
        } catch (\Throwable $e) {
            // Already exists.
        }

        try {
            Schema::table('rice_price_closings', function (Blueprint $table) {
                $table->index(['market', 'close_date'], 'rice_price_closings_market_close_date_idx');
            });
        } catch (\Throwable $e) {
            // Already exists.
        }
    }

    public function down()
    {
        if (! Schema::hasTable('rice_price_closings')) {
            return;
        }

        try {
            Schema::table('rice_price_closings', function (Blueprint $table) {
                $table->dropUnique('rice_price_closings_market_close_date_unique');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('rice_price_closings', function (Blueprint $table) {
                $table->dropIndex('rice_price_closings_market_close_date_idx');
            });
        } catch (\Throwable $e) {
        }

        if (Schema::hasColumn('rice_price_closings', 'market')) {
            Schema::table('rice_price_closings', function (Blueprint $table) {
                $table->dropColumn('market');
            });
        }

        try {
            Schema::table('rice_price_closings', function (Blueprint $table) {
                $table->unique('close_date', 'rice_price_closings_close_date_unique');
            });
        } catch (\Throwable $e) {
        }
    }
}
