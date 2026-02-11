<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWandTypeTable extends Migration
{
    public function up()
    {
        Schema::create('wandType', function (Blueprint $table) {
            $table->id();
            $table->string('type', 256);
            $table->integer('order');
            $table->integer('status');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wandType');
    }
}

