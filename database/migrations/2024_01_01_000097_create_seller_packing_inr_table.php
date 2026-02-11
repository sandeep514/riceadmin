<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellerPackingInrTable extends Migration
{
    public function up()
    {
        Schema::create('sellerPackingINR', function (Blueprint $table) {
            $table->id();
            $table->string('packing', 256);
            $table->text('description');
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sellerPackingINR');
    }
}

