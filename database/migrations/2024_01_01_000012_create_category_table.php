<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryTable extends Migration
{
    public function up()
    {
        Schema::create('category', function (Blueprint $table) {
            $table->id();
            $table->string('category', 255);
            $table->text('description')->nullable();
            $table->string('image', 255)->nullable();
            $table->integer('order')->nullable();
            $table->integer('status')->default(1);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('category');
    }
}

