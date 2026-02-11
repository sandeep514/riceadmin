<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiceBrandFormTable extends Migration
{
    public function up()
    {
        Schema::create('rice_brand_form', function (Blueprint $table) {
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
        Schema::dropIfExists('rice_brand_form');
    }
}

