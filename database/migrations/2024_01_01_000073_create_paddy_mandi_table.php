<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaddyMandiTable extends Migration
{
    public function up()
    {
        Schema::create('paddyMandi', function (Blueprint $table) {
            $table->id();
            $table->string('mandi', 256);
            $table->integer('state_id');
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('paddyMandi');
    }
}

