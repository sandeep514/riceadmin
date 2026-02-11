<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->integer('from');
            $table->integer('to');
            $table->integer('seen')->default(0);
            $table->text('message');
            $table->integer('status')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('from');
            $table->index('to');
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
}

