<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsdPricesTable extends Migration
{
    public function up()
    {
        Schema::create('USD_prices', function (Blueprint $table) {
            $table->id();
            $table->integer('rice');
            $table->string('ricemin', 256);
            $table->string('ricemax', 256);
            $table->string('transportmin', 256);
            $table->string('transportmax', 256);
            $table->string('category', 256);
            $table->string('charges', 256);
            $table->string('dollarrate', 256);
            $table->string('percentageValue', 256);
            $table->string('totalMin', 256);
            $table->string('totalMax', 256);
            $table->string('exchangeRatemin', 256);
            $table->string('exchangeRatemax', 256);
            $table->string('fobmin', 256);
            $table->string('fobmax', 256);
            $table->integer('usd_defaultMaster_id');
            $table->integer('status')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('color_status')->default(3);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at');
            $table->index('usd_defaultMaster_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('USD_prices');
    }
}

