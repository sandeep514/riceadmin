<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValidTillToSellQueryTables extends Migration
{
    public function up()
    {
        Schema::table('future_sell_query_milestone3', function (Blueprint $table) {
            if (! Schema::hasColumn('future_sell_query_milestone3', 'offerPrice')) {
                $table->string('offerPrice', 256)->nullable()->after('quantity');
            }
            if (! Schema::hasColumn('future_sell_query_milestone3', 'validDays')) {
                $table->string('validDays', 256)->nullable()->after('offerPrice');
            }
        });
    }

    public function down()
    {
        Schema::table('future_sell_query_milestone3', function (Blueprint $table) {
            if (Schema::hasColumn('future_sell_query_milestone3', 'validDays')) {
                $table->dropColumn('validDays');
            }
            if (Schema::hasColumn('future_sell_query_milestone3', 'offerPrice')) {
                $table->dropColumn('offerPrice');
            }
        });
    }
}
