<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDefaultValueTable extends Migration
{
    public function up()
    {
        Schema::create('default_value', function (Blueprint $table) {
            $table->id();
            $table->string('localcharges', 256);
            $table->string('financecost', 256);
            $table->string('dollarvalue', 256);
            $table->integer('bagcost')->nullable();
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('default_value');
    }
}

