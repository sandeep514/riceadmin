<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVideoFileToTradeQueryMilestone3Table extends Migration
{
    public function up()
    {
        Schema::table('trade_query_milestone3', function (Blueprint $table) {
            if (! Schema::hasColumn('trade_query_milestone3', 'video_file')) {
                $table->string('video_file', 255)->nullable()->after('packing_file');
            }
        });
    }

    public function down()
    {
        Schema::table('trade_query_milestone3', function (Blueprint $table) {
            if (Schema::hasColumn('trade_query_milestone3', 'video_file')) {
                $table->dropColumn('video_file');
            }
        });
    }
}
