<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQualityMasterTable extends Migration
{
    public function up()
    {
        Schema::create('quality_master', function (Blueprint $table) {
            $table->id();
            $table->string('quality', 256);
            $table->string('quality_name', 256);
            $table->string('quality_type', 256);
            $table->integer('quality_type_status');
            $table->integer('status')->default(1);
            $table->integer('order')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('quality_master');
    }
}

