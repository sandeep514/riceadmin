<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBidTable extends Migration
{
    public function up()
    {
        Schema::create('bid', function (Blueprint $table) {
            $table->id();
            $table->integer('query_id');
            $table->integer('seller_id');
            $table->string('bid_amount', 256);
            $table->string('counter_amount', 256)->default('0');
            $table->string('validTill', 256);
            $table->integer('counter_status')->default(0);
            $table->integer('accept_status')->nullable();
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bid');
    }
}

