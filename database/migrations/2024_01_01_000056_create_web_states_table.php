<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebStatesTable extends Migration
{
    public function up()
    {
        Schema::create('web_states', function (Blueprint $table) {
            $table->id();
            $table->char('state_code', 3)->unique();
            $table->string('state_name', 100)->unique();
            $table->integer('order_no');
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_states');
    }
}

