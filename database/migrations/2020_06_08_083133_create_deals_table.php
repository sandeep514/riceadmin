<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('sntc_no')->index()->nullable();
            $table->string('contract_no');
            $table->unsignedBigInteger('seller')->index();
            $table->unsignedBigInteger('buyer')->index();
            $table->unsignedBigInteger('quantity');
            $table->boolean('is_direct_deal')->default(0);
            $table->string('document')->nullable();
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
        Schema::dropIfExists('contracts');
    }
}
