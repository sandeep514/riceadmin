<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebCartoonAndCylinderProductsTables extends Migration
{
    public function up()
    {
        Schema::create('web_cartoon_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cartoon_type_id')->nullable();
            $table->text('specification')->nullable();
            $table->text('description')->nullable();
            $table->text('additional_information')->nullable();
            $table->unsignedTinyInteger('status')->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->index('cartoon_type_id');
        });

        Schema::create('web_cartoon_product_variants', function (Blueprint $table) {
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
                ->on('web_cartoon_products')
                ->onDelete('cascade');
        });

        Schema::create('web_cylinder_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cylinder_type_id')->nullable();
            $table->text('specification')->nullable();
            $table->text('description')->nullable();
            $table->text('additional_information')->nullable();
            $table->unsignedTinyInteger('status')->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->index('cylinder_type_id');
        });

        Schema::create('web_cylinder_product_variants', function (Blueprint $table) {
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
                ->on('web_cylinder_products')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_cylinder_product_variants');
        Schema::dropIfExists('web_cylinder_products');
        Schema::dropIfExists('web_cartoon_product_variants');
        Schema::dropIfExists('web_cartoon_products');
    }
}
