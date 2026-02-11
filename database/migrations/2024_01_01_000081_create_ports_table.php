<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePortsTable extends Migration
{
    public function up()
    {
        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->string('web_images', 255)->nullable();
            $table->text('banner')->nullable();
            $table->string('state', 191);
            $table->string('route', 191);
            $table->string('price', 191);
            $table->integer('status')->default(1);
            $table->integer('state_order')->nullable();
            $table->integer('route_order')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ports');
    }
}

