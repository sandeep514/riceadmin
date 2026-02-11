<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestimonialTable extends Migration
{
    public function up()
    {
        Schema::create('testimonial', function (Blueprint $table) {
            $table->id();
            $table->string('title', 256)->nullable();
            $table->string('designation', 256)->nullable();
            $table->string('message', 256)->nullable();
            $table->date('date')->nullable();
            $table->string('file', 256)->nullable();
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('testimonial');
    }
}

