<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyerPackingInrTable extends Migration
{
    public function up()
    {
        Schema::create('buyer_packing_INR', function (Blueprint $table) {
            $table->id();
            $table->string('packing', 256);
            $table->string('description', 256);
            $table->integer('status')->default(1);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('buyer_packing_INR');
    }
}

