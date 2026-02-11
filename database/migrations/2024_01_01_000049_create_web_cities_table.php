<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebCitiesTable extends Migration
{
    public function up()
    {
        Schema::create('web_cities', function (Blueprint $table) {
            $table->id();
            $table->string('city_name', 100);
            $table->integer('state_id');
            $table->boolean('is_capital')->default(0);
            $table->integer('population')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_cities');
    }
}

