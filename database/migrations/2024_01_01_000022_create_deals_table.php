<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealsTable extends Migration
{
    public function up()
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('sntc_no')->nullable();
            $table->string('contract_no', 191);
            $table->unsignedBigInteger('seller');
            $table->unsignedBigInteger('buyer');
            $table->unsignedBigInteger('quality');
            $table->boolean('is_direct_deal')->default(0);
            $table->string('image', 191)->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('sntc_no');
            $table->index('seller');
            $table->index('buyer');
            $table->index('quality');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deals');
    }
}

