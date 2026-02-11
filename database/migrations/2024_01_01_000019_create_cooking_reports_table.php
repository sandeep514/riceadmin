<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCookingReportsTable extends Migration
{
    public function up()
    {
        Schema::create('cooking_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sntc_no');
            $table->text('remarks');
            $table->text('image');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('sntc_no');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cooking_reports');
    }
}

