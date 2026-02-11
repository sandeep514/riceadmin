<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiceFormsTable extends Migration
{
    public function up()
    {
        Schema::create('rice_forms', function (Blueprint $table) {
            $table->id();
            $table->string('form_name', 191);
            $table->string('type', 191);
            $table->integer('order')->nullable();
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rice_forms');
    }
}

