<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsdDefaultmasterTable extends Migration
{
    public function up()
    {
        Schema::create('USD_defaultmaster', function (Blueprint $table) {
            $table->id();
            $table->string('bag_size', 256);
            $table->string('bag_type', 256);
            $table->string('bag_cost', 256);
            $table->string('local_freight', 256);
            $table->string('cha', 256);
            $table->string('bank_charges', 256);
            $table->string('ins', 256);
            $table->string('total', 256);
            $table->string('conversion_rate', 256);
            $table->string('PMT_USD', 256);
            $table->integer('status')->default(1);
            $table->integer('order');
            $table->integer('applied_for')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('USD_defaultmaster');
    }
}

