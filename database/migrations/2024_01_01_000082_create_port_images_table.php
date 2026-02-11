<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePortImagesTable extends Migration
{
    public function up()
    {
        Schema::create('port_images', function (Blueprint $table) {
            $table->id();
            $table->string('port', 256);
            $table->text('attachment');
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('port_images');
    }
}

