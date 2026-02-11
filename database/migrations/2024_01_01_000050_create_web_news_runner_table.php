<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebNewsRunnerTable extends Migration
{
    public function up()
    {
        Schema::create('web_news_runner', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->string('type', 20);
            $table->string('newsType', 255)->nullable();
            $table->integer('status')->default(1);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_news_runner');
    }
}

