<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentMastersAndVendorProductsTables extends Migration
{
    public function up()
    {
        Schema::create('lab_equipments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique('name');
            $table->index('status');
        });

        Schema::create('machinery_equipments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique('name');
            $table->index('status');
        });

        Schema::create('web_lab_equipment_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('status')->default(0);
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::create('web_lab_equipment_product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('equipment_id')->nullable();
            $table->string('equipment_name', 255)->nullable();
            $table->decimal('rate', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('catalogue', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('product_id');
            $table->index('equipment_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('web_lab_equipment_products')
                ->onDelete('cascade');
        });

        Schema::create('web_machinery_equipment_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('status')->default(0);
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::create('web_machinery_equipment_product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('equipment_id')->nullable();
            $table->string('equipment_name', 255)->nullable();
            $table->decimal('rate', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('catalogue', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('product_id');
            $table->index('equipment_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('web_machinery_equipment_products')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_machinery_equipment_product_variants');
        Schema::dropIfExists('web_machinery_equipment_products');
        Schema::dropIfExists('web_lab_equipment_product_variants');
        Schema::dropIfExists('web_lab_equipment_products');
        Schema::dropIfExists('machinery_equipments');
        Schema::dropIfExists('lab_equipments');
    }
}
