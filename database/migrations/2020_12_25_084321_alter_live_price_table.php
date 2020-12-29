<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterLivePriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Add new status column in live_prices
        Schema::table('live_prices' , function(Blueprint $table){
            $table->integer('status' )->after('up_down')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('live_prices', function(Blueprint $table){
            $table->drop('status');
        });
    }
}
