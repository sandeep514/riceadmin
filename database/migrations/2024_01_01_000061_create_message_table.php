<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageTable extends Migration
{
    public function up()
    {
        Schema::create('message', function (Blueprint $table) {
            $table->id();
            $table->integer('from');
            $table->integer('to');
            $table->text('message');
            $table->integer('status')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
        });
    }

    public function down()
    {
        Schema::dropIfExists('message');
    }
}

