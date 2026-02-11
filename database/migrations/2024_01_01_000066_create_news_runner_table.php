<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsRunnerTable extends Migration
{
    public function up()
    {
        Schema::create('news_runner', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->string('type', 20);
            $table->integer('status')->default(1);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('news_runner');
    }
}

