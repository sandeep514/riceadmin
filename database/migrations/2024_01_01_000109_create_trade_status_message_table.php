<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradeStatusMessageTable extends Migration
{
    public function up()
    {
        Schema::create('trade_status_message', function (Blueprint $table) {
            $table->id();
            $table->integer('trade_status');
            $table->string('message', 256);
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trade_status_message');
    }
}

