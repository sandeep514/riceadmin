<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDomesticVendorMastersTables extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('domestic_vendor_countries')) {
            Schema::create('domestic_vendor_countries', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->unique('name');
                $table->index('status');
            });
        }

        if (! Schema::hasTable('domestic_vendor_states')) {
            Schema::create('domestic_vendor_states', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('country_id');
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->foreign('country_id')
                    ->references('id')
                    ->on('domestic_vendor_countries')
                    ->restrictOnDelete();
                $table->unique(['country_id', 'name']);
                $table->index('status');
            });
        }

        if (! Schema::hasTable('domestic_vendor_cities')) {
            Schema::create('domestic_vendor_cities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('state_id');
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->foreign('state_id')
                    ->references('id')
                    ->on('domestic_vendor_states')
                    ->restrictOnDelete();
                $table->unique(['state_id', 'name']);
                $table->index('status');
            });
        }

        if (! Schema::hasTable('domestic_vendor_destinations')) {
            Schema::create('domestic_vendor_destinations', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->unique('name');
                $table->index('status');
            });
        }

        if (! Schema::hasTable('domestic_vendor_truck_sizes')) {
            Schema::create('domestic_vendor_truck_sizes', function (Blueprint $table) {
                $table->id();
                $table->decimal('size', 8, 2);
                $table->string('label', 255)->nullable();
                $table->text('description')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->unique('size');
                $table->index('status');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('domestic_vendor_truck_sizes');
        Schema::dropIfExists('domestic_vendor_destinations');
        Schema::dropIfExists('domestic_vendor_cities');
        Schema::dropIfExists('domestic_vendor_states');
        Schema::dropIfExists('domestic_vendor_countries');
    }
}
