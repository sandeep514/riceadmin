<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddUniqueIndexToLivePriceClosing extends Migration
{
    public function up()
    {
        // First remove duplicate rows, keeping only the latest one per (name, form, cropYear, state)
        DB::statement("
            DELETE t1 FROM live_price_closing t1
            INNER JOIN live_price_closing t2
            ON t1.name = t2.name
            AND t1.form = t2.form
            AND t1.cropYear = t2.cropYear
            AND t1.state = t2.state
            AND t1.id < t2.id
        ");

        Schema::table('live_price_closing', function (Blueprint $table) {
            $table->unique(['name', 'form', 'cropYear', 'state'], 'live_price_closing_unique');
        });
    }

    public function down()
    {
        Schema::table('live_price_closing', function (Blueprint $table) {
            $table->dropUnique('live_price_closing_unique');
        });
    }
}
