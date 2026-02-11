<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactUsTable extends Migration
{
    public function up()
    {
        Schema::create('contact_us', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 256);
            $table->string('email', 256);
            $table->integer('status');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contact_us');
    }
}

