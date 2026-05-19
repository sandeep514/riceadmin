<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReportFileToSellQueryTables extends Migration
{
    public function up()
    {
        Schema::table('sell_query_milestone3', function (Blueprint $table) {
            if (! Schema::hasColumn('sell_query_milestone3', 'report_file')) {
                $table->string('report_file', 255)->nullable()->after('extra_file');
            }
        });

        Schema::table('future_sell_query_milestone3', function (Blueprint $table) {
            if (! Schema::hasColumn('future_sell_query_milestone3', 'report_file')) {
                $table->string('report_file', 255)->nullable()->after('extra_file');
            }
        });
    }

    public function down()
    {
        Schema::table('sell_query_milestone3', function (Blueprint $table) {
            if (Schema::hasColumn('sell_query_milestone3', 'report_file')) {
                $table->dropColumn('report_file');
            }
        });

        Schema::table('future_sell_query_milestone3', function (Blueprint $table) {
            if (Schema::hasColumn('future_sell_query_milestone3', 'report_file')) {
                $table->dropColumn('report_file');
            }
        });
    }
}
