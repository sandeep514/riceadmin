<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradeLikeTable extends Migration
{
    public function up()
    {
        Schema::create('trade_like', function (Blueprint $table) {
            $table->id();
            $table->integer('tradeId');
            $table->integer('userId');
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trade_like');
    }
}

