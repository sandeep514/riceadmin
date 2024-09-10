<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSamplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('samples', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('supplier')->index();
            $table->unsignedBigInteger('quality')->index();
            $table->unsignedBigInteger('packing')->index();
            $table->unsignedBigInteger('packing_type')->index();
            $table->string('no_of_bags')->nullable();
            $table->string('bags_qty')->nullable();
            $table->integer('qty')->nullable();
            $table->string('offer');
            $table->text('photo')->nullable();
            $table->boolean('courier_status')->default(0);
            $table->unsignedBigInteger('courier_id')->index()->nullable();
            $table->unsignedBigInteger('user_id')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('samples');
    }
}
