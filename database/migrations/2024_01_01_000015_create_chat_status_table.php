<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatStatusTable extends Migration
{
    public function up()
    {
        Schema::create('chatStatus', function (Blueprint $table) {
            $table->id();
            $table->integer('status');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chatStatus');
    }
}

