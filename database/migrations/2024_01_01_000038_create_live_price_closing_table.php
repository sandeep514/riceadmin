<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLivePriceClosingTable extends Migration
{
    public function up()
    {
        Schema::create('live_price_closing', function (Blueprint $table) {
            $table->id();
            $table->integer('trade_for')->nullable();
            $table->integer('farming_type')->nullable();
            $table->integer('name')->nullable();
            $table->integer('form')->nullable();
            $table->string('cropYear', 50)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('opening', 50)->nullable();
            $table->string('closing', 50)->nullable();
            $table->tinyInteger('status')->nullable()->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('live_price_closing');
    }
}

