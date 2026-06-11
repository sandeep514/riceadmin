<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValidDatetimeForIsNewToTradeQueryMilestone3Table extends Migration
{
    public function up()
    {
        Schema::table('trade_query_milestone3', function (Blueprint $table) {
            if (! Schema::hasColumn('trade_query_milestone3', 'valid_datetime_for_is_new')) {
                $table->dateTime('valid_datetime_for_is_new')->nullable()->after('is_new');
            }
        });
    }

    public function down()
    {
        Schema::table('trade_query_milestone3', function (Blueprint $table) {
            if (Schema::hasColumn('trade_query_milestone3', 'valid_datetime_for_is_new')) {
                $table->dropColumn('valid_datetime_for_is_new');
            }
        });
    }
}
