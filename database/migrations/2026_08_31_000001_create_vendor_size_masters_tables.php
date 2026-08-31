<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorSizeMastersTables extends Migration
{
    public function up()
    {
        Schema::create('bag_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('size', 255);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique('size');
            $table->index('status');
        });

        Schema::create('carton_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('size', 255);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique('size');
            $table->index('status');
        });

        Schema::create('cylinder_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('size', 255);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique('size');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cylinder_sizes');
        Schema::dropIfExists('carton_sizes');
        Schema::dropIfExists('bag_sizes');
    }
}
