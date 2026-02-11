<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLivePricesTable extends Migration
{
    public function up()
    {
        Schema::create('live_prices', function (Blueprint $table) {
            $table->id();
            $table->integer('tradeFor')->default(1);
            $table->integer('farmingType')->default(1);
            $table->string('name', 255)->nullable();
            $table->string('form', 255)->nullable();
            $table->integer('cropGrade');
            $table->integer('cropYear');
            $table->string('min_price', 100)->nullable();
            $table->string('max_price', 100)->nullable();
            $table->string('state', 100);
            $table->string('up_down', 50);
            $table->string('opening', 255)->nullable();
            $table->string('closing', 256)->nullable();
            $table->string('monthStart', 256)->nullable();
            $table->string('monthEnd', 256)->nullable();
            $table->integer('status')->default(1);
            $table->integer('is_updated_by_admin')->default(0);
            $table->integer('state_order')->nullable();
            $table->integer('name_order')->nullable();
            $table->integer('form_order')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->index('name');
            $table->index('form');
        });
    }

    public function down()
    {
        Schema::dropIfExists('live_prices');
    }
}

