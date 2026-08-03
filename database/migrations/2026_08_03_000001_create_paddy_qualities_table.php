<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaddyQualitiesTable extends Migration
{
    public function up()
    {
        Schema::create('paddy_qualities', function (Blueprint $table) {
            $table->id();
            $table->string('quality', 255);
            $table->text('description')->nullable();
            $table->unsignedInteger('order')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('paddy_qualities');
    }
}
