<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHotdealToPaddyTradesTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('paddy_trades', 'hotdeal')) {
            Schema::table('paddy_trades', function (Blueprint $table) {
                $table->unsignedTinyInteger('hotdeal')->default(0)->after('is_new');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('paddy_trades', 'hotdeal')) {
            Schema::table('paddy_trades', function (Blueprint $table) {
                $table->dropColumn('hotdeal');
            });
        }
    }
}
