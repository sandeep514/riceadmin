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
            $table->string('product_name', 255);
            $table->unsignedBigInteger('rice_name_id')->nullable();
            $table->unsignedBigInteger('rice_form_id')->nullable();
            $table->string('rice_form', 255)->nullable();
            $table->string('bag_color', 64)->nullable();
            $table->string('print_type', 64)->nullable();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();

            $table->index('user_id');
            $table->index('rice_name_id');
        });

        Schema::create('web_rice_bag_product_packing_sizes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('packing_id')->nullable();
            $table->string('packing', 255)->nullable();
            $table->string('packing_form', 64)->nullable();
            $table->decimal('available_quantity', 12, 2)->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('product_id');
            $table->index('packing_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('web_rice_bag_products')
                ->onDelete('cascade');
        });

        Schema::create('web_rice_bag_product_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('file_name', 255);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('web_rice_bag_products')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_rice_bag_product_images');
        Schema::dropIfExists('web_rice_bag_product_packing_sizes');
        Schema::dropIfExists('web_rice_bag_products');
    }
}
