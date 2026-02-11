<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFreeTrialMonthsTable extends Migration
{
    public function up()
    {
        Schema::create('freeTrialMonths', function (Blueprint $table) {
            $table->id();
            $table->string('month', 256);
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('freeTrialMonths');
    }
}

