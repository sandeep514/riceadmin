<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandAvailabilityTable extends Migration
{
    public function up()
    {
        Schema::create('brand_availability', function (Blueprint $table) {
            $table->id();
            $table->integer('brand_id');
            $table->integer('state_id');
            $table->integer('city_id');
            $table->boolean('status')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('brand_availability');
    }
}

