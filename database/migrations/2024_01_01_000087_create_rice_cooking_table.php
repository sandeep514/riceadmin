<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiceCookingTable extends Migration
{
    public function up()
    {
        Schema::create('rice_cooking', function (Blueprint $table) {
            $table->id();
            $table->integer('sample_id')->nullable();
            $table->string('sample_soak_qty', 255)->nullable();
            $table->timestamp('soak_time_starts')->nullable();
            $table->timestamp('soak_time_end')->nullable();
            $table->timestamp('cooking_start_time')->nullable();
            $table->timestamp('cooking_end_time')->nullable();
            $table->string('weight_before_soak', 255)->nullable();
            $table->string('weight_after_soak', 255)->nullable();
            $table->string('weight_after_cook', 255)->nullable();
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rice_cooking');
    }
}

