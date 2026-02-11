<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldRunnersTable extends Migration
{
    public function up()
    {
        Schema::create('field_runners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('zone');
            $table->unsignedBigInteger('designation');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('user_id');
            $table->index('zone');
            $table->index('designation');
        });
    }

    public function down()
    {
        Schema::dropIfExists('field_runners');
    }
}

