<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCylinderTypesTable extends Migration
{
    public function up()
    {
        Schema::create('cylinder_types', function (Blueprint $table) {
            $table->id();
            $table->string('type', 255);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique('type');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cylinder_types');
    }
}
