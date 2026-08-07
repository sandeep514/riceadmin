<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPackingIdToPaddySellQueriesTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('paddy_sell_queries', 'packing_id')) {
            Schema::table('paddy_sell_queries', function (Blueprint $table) {
                $table->unsignedBigInteger('packing_id')->nullable()->after('hand_combined');
                $table->index('packing_id');
            });
        }

        if (Schema::hasTable('paddy_trades') && ! Schema::hasColumn('paddy_trades', 'packing_id')) {
            Schema::table('paddy_trades', function (Blueprint $table) {
                $table->unsignedBigInteger('packing_id')->nullable()->after('hand_combined');
                $table->index('packing_id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('paddy_sell_queries', 'packing_id')) {
            Schema::table('paddy_sell_queries', function (Blueprint $table) {
                $table->dropIndex(['packing_id']);
                $table->dropColumn('packing_id');
            });
        }

        if (Schema::hasTable('paddy_trades') && Schema::hasColumn('paddy_trades', 'packing_id')) {
            Schema::table('paddy_trades', function (Blueprint $table) {
                $table->dropIndex(['packing_id']);
                $table->dropColumn('packing_id');
            });
        }
    }
}
