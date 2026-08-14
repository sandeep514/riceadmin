<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLotNumberAndCropYearToPaddyTradesTable extends Migration
{
    public function up()
    {
        Schema::table('paddy_trades', function (Blueprint $table) {
            if (! Schema::hasColumn('paddy_trades', 'lot_number')) {
                $table->string('lot_number', 100)->nullable()->after('additional_information');
            }
            if (! Schema::hasColumn('paddy_trades', 'crop_year')) {
                $table->string('crop_year', 50)->nullable()->after('lot_number');
            }
        });
    }

    public function down()
    {
        Schema::table('paddy_trades', function (Blueprint $table) {
            if (Schema::hasColumn('paddy_trades', 'crop_year')) {
                $table->dropColumn('crop_year');
            }
            if (Schema::hasColumn('paddy_trades', 'lot_number')) {
                $table->dropColumn('lot_number');
            }
        });
    }
}
