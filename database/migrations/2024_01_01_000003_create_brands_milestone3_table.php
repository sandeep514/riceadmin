<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandsMilestone3Table extends Migration
{
    public function up()
    {
        Schema::create('brands_milestone3', function (Blueprint $table) {
            $table->id();
            $table->string('name', 256);
            $table->string('image', 256);
            $table->integer('status')->default(1);
            $table->integer('orders');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('brands_milestone3');
    }
}

