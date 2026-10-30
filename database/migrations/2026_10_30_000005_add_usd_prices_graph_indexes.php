<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUsdPricesGraphIndexes extends Migration
{
    public function up()
    {
        $existing = collect(DB::select('SHOW INDEX FROM `USD_prices`'))
            ->pluck('Key_name')
            ->unique()
            ->all();

        Schema::table('USD_prices', function (Blueprint $table) use ($existing) {
            if (! in_array('usd_prices_rice_created_at_index', $existing, true)) {
                $table->index(['rice', 'created_at'], 'usd_prices_rice_created_at_index');
            }
            if (! in_array('usd_prices_rice_master_id_index', $existing, true)) {
                $table->index(['rice', 'usd_defaultMaster_id'], 'usd_prices_rice_master_id_index');
            }
        });
    }

    public function down()
    {
        $existing = collect(DB::select('SHOW INDEX FROM `USD_prices`'))
            ->pluck('Key_name')
            ->unique()
            ->all();

        Schema::table('USD_prices', function (Blueprint $table) use ($existing) {
            if (in_array('usd_prices_rice_created_at_index', $existing, true)) {
                $table->dropIndex('usd_prices_rice_created_at_index');
            }
            if (in_array('usd_prices_rice_master_id_index', $existing, true)) {
                $table->dropIndex('usd_prices_rice_master_id_index');
            }
        });
    }
}
