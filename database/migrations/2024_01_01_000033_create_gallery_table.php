<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGalleryTable extends Migration
{
    public function up()
    {
        Schema::create('gallery', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->text('description');
            $table->text('attachment');
            $table->string('attachment2', 256)->nullable();
            $table->text('spec');
            $table->string('amount', 191);
            $table->string('type', 256);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('gallery');
    }
}

