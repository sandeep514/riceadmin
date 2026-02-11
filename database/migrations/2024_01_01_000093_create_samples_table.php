<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSamplesTable extends Migration
{
    public function up()
    {
        Schema::create('samples', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('supplier');
            $table->unsignedBigInteger('quality');
            $table->unsignedBigInteger('packing');
            $table->unsignedBigInteger('packing_type');
            $table->integer('qty')->nullable();
            $table->string('no_of_bags', 50)->nullable();
            $table->string('bags_qty', 50)->nullable();
            $table->text('photo')->nullable();
            $table->boolean('courier_status')->default(0);
            $table->unsignedBigInteger('courier_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('supplier');
            $table->index('quality');
            $table->index('packing');
            $table->index('packing_type');
            $table->index('courier_id');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('samples');
    }
}

