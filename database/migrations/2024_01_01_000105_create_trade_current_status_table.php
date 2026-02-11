<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradeCurrentStatusTable extends Migration
{
    public function up()
    {
        Schema::create('trade_current_status', function (Blueprint $table) {
            $table->id();
            $table->integer('currentStatus');
            $table->string('message', 256);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trade_current_status');
    }
}

