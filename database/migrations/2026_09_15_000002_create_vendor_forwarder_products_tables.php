<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateVendorForwarderProductsTables extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('vendor_forwarder_charge_titles')) {
            Schema::create('vendor_forwarder_charge_titles', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->unique('name');
                $table->index('status');
            });
        }

        $now = now();
        foreach ([
            'Ori THC',
            'IHC',
            'BL',
            'Seal + Maintenance Fee',
            'AMS',
            'OWS',
            'O/F',
            'Seaway BL',
            'Surrender BL',
        ] as $name) {
            $exists = DB::table('vendor_forwarder_charge_titles')->where('name', $name)->exists();
            if ($exists) {
                continue;
            }
            DB::table('vendor_forwarder_charge_titles')->insert([
                'name' => $name,
                'description' => null,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (! Schema::hasTable('web_forwarder_products')) {
            Schema::create('web_forwarder_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('port_type_id')->nullable()->index();
                $table->string('port_type', 255)->nullable();
                $table->unsignedBigInteger('icd_location_id')->nullable()->index();
                $table->string('icd_location', 255)->nullable();
                $table->unsignedBigInteger('indian_port_id')->nullable()->index();
                $table->string('port_location', 255)->nullable();
                $table->unsignedBigInteger('region_id')->nullable()->index();
                $table->unsignedBigInteger('country_id')->nullable()->index();
                $table->unsignedBigInteger('destination_port_id')->nullable()->index();
                $table->string('destination', 255)->nullable();
                $table->text('additional_information')->nullable();
                $table->text('admin_message')->nullable();
                $table->unsignedTinyInteger('status')->default(0)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('web_forwarder_product_sizes')) {
            Schema::create('web_forwarder_product_sizes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('container_size_id')->nullable()->index();
                $table->unsignedSmallInteger('size')->nullable()->index();
                $table->timestamps();

                $table->foreign('product_id')
                    ->references('id')
                    ->on('web_forwarder_products')
                    ->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('web_forwarder_charges')) {
            Schema::create('web_forwarder_charges', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('title_id')->nullable()->index();
                $table->string('title', 255)->nullable();
                $table->string('currency', 10)->default('INR');
                $table->string('charges', 50)->nullable();
                $table->string('exchange_rate', 50)->nullable();
                $table->string('inr_amount', 50)->nullable();
                $table->string('remarks', 255)->nullable();
                $table->unsignedTinyInteger('is_other')->default(0);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('product_id')
                    ->references('id')
                    ->on('web_forwarder_products')
                    ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('web_forwarder_charges');
        Schema::dropIfExists('web_forwarder_product_sizes');
        Schema::dropIfExists('web_forwarder_products');
        Schema::dropIfExists('vendor_forwarder_charge_titles');
    }
}
