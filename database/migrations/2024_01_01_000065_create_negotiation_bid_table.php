<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNegotiationBidTable extends Migration
{
    public function up()
    {
        Schema::create('negotiation_bid', function (Blueprint $table) {
            $table->id();
            $table->integer('bid_id');
            $table->integer('vendor_id');
            $table->integer('buyer_id');
            $table->string('negotiation_amount', 256);
            $table->integer('status');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('negotiation_bid');
    }
}

