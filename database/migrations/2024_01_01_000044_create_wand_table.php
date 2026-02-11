<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWandTable extends Migration
{
    public function up()
    {
        Schema::create('wand', function (Blueprint $table) {
            $table->id();
            $table->integer('RiceNameId');
            $table->integer('wandTypeId');
            $table->string('value', 256);
            $table->integer('order')->nullable();
            $table->integer('status');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wand');
    }
}

