<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffersTable extends Migration
{
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date');
            $table->string('sntc_no', 191);
            $table->string('offer_price', 191);
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('sntc_no');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('offers');
    }
}

