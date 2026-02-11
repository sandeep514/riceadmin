<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaddyStatesTable extends Migration
{
    public function up()
    {
        Schema::create('paddyStates', function (Blueprint $table) {
            $table->id();
            $table->string('state', 256);
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('paddyStates');
    }
}

