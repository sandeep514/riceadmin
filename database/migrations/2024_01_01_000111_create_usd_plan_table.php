<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsdPlanTable extends Migration
{
    public function up()
    {
        Schema::create('USDPlan', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name', 256);
            $table->string('plan_desc', 256);
            $table->integer('valid_months');
            $table->integer('actual_price');
            $table->integer('discounted_prie');
            $table->string('actual_price_usd', 256)->nullable();
            $table->string('discounted_price_usd', 256)->nullable();
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('USDPlan');
    }
}

