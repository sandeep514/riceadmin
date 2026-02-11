<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiceTypesTable extends Migration
{
    public function up()
    {
        Schema::create('rice_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rice_types');
    }
}

