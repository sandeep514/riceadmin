<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDomestictransportTable extends Migration
{
    public function up()
    {
        Schema::create('domestictransport', function (Blueprint $table) {
            $table->id();
            $table->string('from', 256);
            $table->string('to', 256);
            $table->string('upto', 256);
            $table->string('pmt', 256);
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('domestictransport');
    }
}

