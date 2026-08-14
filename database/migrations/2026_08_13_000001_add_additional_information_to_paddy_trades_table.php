<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalInformationToPaddyTradesTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('paddy_trades', 'additional_information')) {
            Schema::table('paddy_trades', function (Blueprint $table) {
                $table->text('additional_information')->nullable()->after('remarks');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('paddy_trades', 'additional_information')) {
            Schema::table('paddy_trades', function (Blueprint $table) {
                $table->dropColumn('additional_information');
            });
        }
    }
}
