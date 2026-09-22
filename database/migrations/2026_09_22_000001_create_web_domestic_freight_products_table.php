<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebDomesticFreightProductsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('web_domestic_freight_products')) {
            Schema::create('web_domestic_freight_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('state_id')->nullable()->index();
                $table->string('state', 255)->nullable();
                $table->unsignedBigInteger('city_id')->nullable()->index();
                $table->string('city', 255)->nullable();
                $table->unsignedBigInteger('destination_id')->nullable()->index();
                $table->string('destination', 255)->nullable();
                $table->unsignedBigInteger('truck_size_id')->nullable()->index();
                $table->string('truck_size', 255)->nullable();
                $table->string('price', 64);
                $table->text('admin_message')->nullable();
                $table->unsignedTinyInteger('status')->default(0)->index();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('web_domestic_freight_products');
    }
}
