<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebRiceBagProductsTables extends Migration
{
    public function up()
    {
        Schema::create('web_rice_bag_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('bag_type_id')->nullable();
            $table->text('specification')->nullable();
            $table->text('description')->nullable();
            $table->text('additional_information')->nullable();
            $table->unsignedBigInteger('packing_form_id')->nullable();
            $table->string('packing_form', 64)->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();

            $table->index('user_id');
            $table->index('bag_type_id');
            $table->index('packing_form_id');
        });

        // packing_sizes[] payload: packing_size_id, packing_size, rate, gst,
        // total_price, bag_size, bag_weight, image
        Schema::create('web_rice_bag_product_packing_sizes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('packing_size_id')->nullable();
            $table->string('packing_size', 255)->nullable();
            $table->decimal('rate', 12, 2)->nullable();
            $table->decimal('gst', 8, 2)->nullable();
            $table->decimal('total_price', 12, 2)->nullable();
            $table->string('bag_size', 255)->nullable();
            $table->string('bag_weight', 64)->nullable();
            $table->string('image', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('product_id');
            $table->index('packing_size_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('web_rice_bag_products')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_rice_bag_product_packing_sizes');
        Schema::dropIfExists('web_rice_bag_products');
    }
}
