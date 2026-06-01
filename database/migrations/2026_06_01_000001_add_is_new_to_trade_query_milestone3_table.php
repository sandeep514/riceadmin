<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsNewToTradeQueryMilestone3Table extends Migration
{
    public function up()
    {
        Schema::table('trade_query_milestone3', function (Blueprint $table) {
            if (! Schema::hasColumn('trade_query_milestone3', 'is_new')) {
                $table->unsignedTinyInteger('is_new')->default(0)->after('hotdeal');
            }
        });
    }

    public function down()
    {
        Schema::table('trade_query_milestone3', function (Blueprint $table) {
            if (Schema::hasColumn('trade_query_milestone3', 'is_new')) {
                $table->dropColumn('is_new');
            }
        });
    }
}
