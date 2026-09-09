<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCleaningAgentVendorProductsTables extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('web_cleaning_agent_products')) {
            Schema::create('web_cleaning_agent_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedTinyInteger('container_20_ft')->default(0);
                $table->unsignedTinyInteger('container_40_ft')->default(0);
                $table->string('port_type', 255)->nullable();
                $table->string('icd_location', 255)->nullable();
                $table->string('port_location', 255)->nullable();
                $table->string('destination', 255)->nullable();
                $table->text('additional_information')->nullable();
                $table->unsignedTinyInteger('status')->default(0)->index();
                $table->timestamps();
            });
        }

        // Meta map: master particular_id and/or free-text other particular (not inserted into master).
        if (! Schema::hasTable('cleaning_agent_particulars_map')) {
            Schema::create('cleaning_agent_particulars_map', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('particular_id')->nullable()->index();
                $table->string('particular_name', 255)->nullable();
                $table->decimal('rate', 12, 2)->nullable();
                $table->unsignedTinyInteger('is_other')->default(0);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('product_id')
                    ->references('id')
                    ->on('web_cleaning_agent_products')
                    ->onDelete('cascade');
            });
        }

        // Seed UI particulars into existing master (skip if already present).
        if (Schema::hasTable('vendor_container_particulars')) {
            $now = now();
            $names = [
                'CFS Composite Charge',
                'Phyto Certificate',
                'Fumigation Charges',
                'LOLO',
                'Stack Fumigation',
                'Transportation Charges',
                'VGM',
                'Service Charge',
                'Railway THC',
                'Inhand Haulage',
            ];

            foreach ($names as $name) {
                $exists = DB::table('vendor_container_particulars')
                    ->where('particular', $name)
                    ->exists();
                if ($exists) {
                    continue;
                }

                DB::table('vendor_container_particulars')->insert([
                    'particular' => $name,
                    'description' => null,
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('cleaning_agent_particulars_map');
        Schema::dropIfExists('web_cleaning_agent_products');
    }
}
