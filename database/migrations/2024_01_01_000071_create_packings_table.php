<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackingsTable extends Migration
{
    public function up()
    {
        Schema::create('packings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 191);
            $table->string('value', 191);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('packings');
    }
}

