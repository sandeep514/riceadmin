<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiceNamesTable extends Migration
{
    public function up()
    {
        Schema::create('rice_names', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('from_month', 256)->nullable();
            $table->string('end_month', 256)->nullable();
            $table->string('type', 191);
            $table->integer('type_status')->default(1);
            $table->integer('order')->nullable();
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rice_names');
    }
}

