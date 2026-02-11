<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCityZonesTable extends Migration
{
    public function up()
    {
        Schema::create('city_zones', function (Blueprint $table) {
            $table->id();
            $table->string('zone_area', 191);
            $table->unsignedBigInteger('city');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('city');
        });
    }

    public function down()
    {
        Schema::dropIfExists('city_zones');
    }
}

