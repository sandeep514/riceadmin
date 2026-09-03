<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValidDatetimeForIsNewToPaddyTradesTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('paddy_trades', 'valid_datetime_for_is_new')) {
            Schema::table('paddy_trades', function (Blueprint $table) {
                $table->dateTime('valid_datetime_for_is_new')->nullable()->after('is_new');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('paddy_trades', 'valid_datetime_for_is_new')) {
            Schema::table('paddy_trades', function (Blueprint $table) {
                $table->dropColumn('valid_datetime_for_is_new');
            });
        }
    }
}
