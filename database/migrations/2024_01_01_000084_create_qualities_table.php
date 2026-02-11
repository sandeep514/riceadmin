<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQualitiesTable extends Migration
{
    public function up()
    {
        Schema::create('qualities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('type', 191);
            $table->string('category', 191);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('qualities');
    }
}

