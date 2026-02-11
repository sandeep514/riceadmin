<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOceanFreightTable extends Migration
{
    public function up()
    {
        Schema::create('ocean_freight', function (Blueprint $table) {
            $table->id();
            $table->integer('sno');
            $table->string('region', 256);
            $table->string('country', 256);
            $table->string('port', 256);
            $table->string('freight_21MT', 256);
            $table->string('freight_25MT', 256);
            $table->string('freight_21MT_1MT', 256)->nullable();
            $table->string('freight_25MT_1MT', 256)->nullable();
            $table->string('mobile_code', 256)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ocean_freight');
    }
}

