<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSampleLabReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sample_lab_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sntc_no')->index();
            $table->string('length');
            $table->string('ad_mixture');
            $table->string('sub_ad_mixture')->nullable();
            $table->string('moisture');
            $table->string('kett')->nullable();
            $table->string('broken');
            $table->string('dd');
            $table->string('chalky');
            $table->string('brown_layer');
            $table->string('stone');
            $table->string('inmature');
            $table->string('broken_pin');
            $table->string('cooking');
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
        Schema::dropIfExists('sample_lab_reports');
    }
}
