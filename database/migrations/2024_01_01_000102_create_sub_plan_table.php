<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubPlanTable extends Migration
{
    public function up()
    {
        Schema::create('sub_plan', function (Blueprint $table) {
            $table->id();
            $table->string('name', 256);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sub_plan');
    }
}

