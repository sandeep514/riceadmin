<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsNewToPaddyTradesTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('paddy_trades', 'is_new')) {
            Schema::table('paddy_trades', function (Blueprint $table) {
                $table->unsignedTinyInteger('is_new')->default(0)->after('status');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('paddy_trades', 'is_new')) {
            Schema::table('paddy_trades', function (Blueprint $table) {
                $table->dropColumn('is_new');
            });
        }
    }
}
