<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanTable extends Migration
{
    public function up()
    {
        Schema::create('plan', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name', 256);
            $table->text('sub_plan');
            $table->text('chart_int');
            $table->integer('price');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
        });
    }

    public function down()
    {
        Schema::dropIfExists('plan');
    }
}

