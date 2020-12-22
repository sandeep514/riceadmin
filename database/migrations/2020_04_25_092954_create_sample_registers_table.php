<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSampleRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sample_registers', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('sntc_no');
            $table->unsignedBigInteger('supplier')->index();
            $table->unsignedBigInteger('quality')->index();
            $table->unsignedBigInteger('packing')->index();
            $table->unsignedBigInteger('packing_type')->index();
            $table->string('no_of_bags');
            $table->string('qty_per_bag');
            $table->integer('seller_qty');
            $table->integer('seller_offer');
            $table->text('photo')->nullable();
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
        Schema::dropIfExists('sample_registers');
    }
}
