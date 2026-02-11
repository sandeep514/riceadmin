<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaddyPricesTable extends Migration
{
    public function up()
    {
        Schema::create('paddy_prices', function (Blueprint $table) {
            $table->id();
            $table->integer('mandi');
            $table->integer('state');
            $table->integer('quality_id');
            $table->string('hand_cutting_price', 256);
            $table->string('machine_cutting_price', 256);
            $table->string('moisture', 256);
            $table->string('total_arrivals', 256);
            $table->string('change', 256)->default('stable');
            $table->boolean('status')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('paddy_prices');
    }
}

