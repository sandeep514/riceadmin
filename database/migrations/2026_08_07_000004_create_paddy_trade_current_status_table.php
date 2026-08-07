<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePaddyTradeCurrentStatusTable extends Migration
{
    public function up()
    {
        Schema::create('paddy_trade_current_status', function (Blueprint $table) {
            $table->id();
            $table->integer('currentStatus')->default(1); // 1 open, 11 closed, 12 hold
            $table->string('message', 256)->nullable();
            $table->timestamps();
        });

        DB::table('paddy_trade_current_status')->insert([
            'id' => 1,
            'currentStatus' => 1,
            'message' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('paddy_trade_current_status');
    }
}
