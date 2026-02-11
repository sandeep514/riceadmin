<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSampleRegistersTable extends Migration
{
    public function up()
    {
        Schema::create('sample_registers', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('sntc_no');
            $table->unsignedBigInteger('supplier');
            $table->unsignedBigInteger('quality');
            $table->unsignedBigInteger('packing');
            $table->unsignedBigInteger('packing_type');
            $table->string('no_of_bags', 100);
            $table->string('qty_per_bag', 100)->nullable();
            $table->integer('seller_qty');
            $table->integer('seller_offer');
            $table->text('photo')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('supplier');
            $table->index('quality');
            $table->index('packing');
            $table->index('packing_type');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sample_registers');
    }
}

