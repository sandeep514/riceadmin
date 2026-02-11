<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHotdealacceptTable extends Migration
{
    public function up()
    {
        Schema::create('hotdealaccept', function (Blueprint $table) {
            $table->id();
            $table->integer('hotdeal_id');
            $table->integer('buyer_id');
            $table->integer('status');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hotdealaccept');
    }
}

