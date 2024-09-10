<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLivePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('live_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('name')->index();
            $table->unsignedBigInteger('form')->index();
            $table->string('min_price')->nullable();
            $table->string('max_price')->nullable();
            $table->string('state');
            $table->string('up_down');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('live_prices');
    }
}
