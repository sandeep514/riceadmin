<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationTable extends Migration
{
    public function up()
    {
        Schema::create('notification', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 256);
            $table->string('title', 256)->nullable();
            $table->text('message');
            $table->string('userAppType', 256);
            $table->integer('status')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification');
    }
}

