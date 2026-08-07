<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaddyTradeInterestedTable extends Migration
{
    public function up()
    {
        Schema::create('paddy_trade_interested', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paddy_trade_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();

            $table->index('paddy_trade_id');
            $table->index('user_id');
            $table->unique(['paddy_trade_id', 'user_id'], 'paddy_trade_user_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('paddy_trade_interested');
    }
}
