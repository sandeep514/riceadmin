<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestimonialVideoTable extends Migration
{
    public function up()
    {
        Schema::create('testimonial_video', function (Blueprint $table) {
            $table->id();
            $table->string('title', 256)->nullable();
            $table->text('file')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('testimonial_video');
    }
}

