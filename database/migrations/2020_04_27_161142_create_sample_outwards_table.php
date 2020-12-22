<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSampleOutwardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sample_outwards', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('sntc_no');
            $table->unsignedBigInteger('buyer')->index();
            $table->unsignedBigInteger('quality')->index();
            $table->string('bag_type');
            $table->string('no_of_bags');
            $table->string('qty_per_bag')->nullable();
            $table->integer('qty')->nullable();
            $table->string('awb_no');
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
        Schema::dropIfExists('sample_outwards');
    }
}
