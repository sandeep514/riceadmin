<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropValidTillFromSellQueryTables extends Migration
{
    public function up()
    {
        Schema::table('sell_query_milestone3', function (Blueprint $table) {
            if (Schema::hasColumn('sell_query_milestone3', 'valid_till')) {
                $table->dropColumn('valid_till');
            }
        });

        Schema::table('future_sell_query_milestone3', function (Blueprint $table) {
            if (Schema::hasColumn('future_sell_query_milestone3', 'valid_till')) {
                $table->dropColumn('valid_till');
            }
        });
    }

    public function down()
    {
        Schema::table('sell_query_milestone3', function (Blueprint $table) {
            if (! Schema::hasColumn('sell_query_milestone3', 'valid_till')) {
                $table->dateTime('valid_till')->nullable()->after('validDays');
            }
        });

        Schema::table('future_sell_query_milestone3', function (Blueprint $table) {
            if (! Schema::hasColumn('future_sell_query_milestone3', 'valid_till')) {
                $table->dateTime('valid_till')->nullable()->after('validDays');
            }
        });
    }
}
