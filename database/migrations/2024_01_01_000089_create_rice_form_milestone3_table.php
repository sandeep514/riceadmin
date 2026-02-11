<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiceFormMilestone3Table extends Migration
{
    public function up()
    {
        Schema::create('rice_form_milestone3', function (Blueprint $table) {
            $table->id();
            $table->string('name', 256);
            $table->integer('order');
            $table->integer('status')->default(1);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rice_form_milestone3');
    }
}

