<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegionAndCountryToDestinationPorts extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('vendor_destination_regions')) {
            Schema::create('vendor_destination_regions', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->unique('name');
                $table->index('status');
            });
        }

        if (! Schema::hasTable('vendor_destination_countries')) {
            Schema::create('vendor_destination_countries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('region_id')->index();
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->unique(['region_id', 'name']);
                $table->index('status');
            });
        }

        if (Schema::hasTable('vendor_destination_ports')) {
            Schema::table('vendor_destination_ports', function (Blueprint $table) {
                if (! Schema::hasColumn('vendor_destination_ports', 'region_id')) {
                    $table->unsignedBigInteger('region_id')->nullable()->after('id')->index();
                }
                if (! Schema::hasColumn('vendor_destination_ports', 'country_id')) {
                    $table->unsignedBigInteger('country_id')->nullable()->after('region_id')->index();
                }
            });

            $this->replaceNameUniqueWithCountryNameUnique();
        }
    }

    public function down()
    {
        if (Schema::hasTable('vendor_destination_ports')) {
            Schema::table('vendor_destination_ports', function (Blueprint $table) {
                foreach (['country_id', 'region_id'] as $col) {
                    if (Schema::hasColumn('vendor_destination_ports', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        Schema::dropIfExists('vendor_destination_countries');
        Schema::dropIfExists('vendor_destination_regions');
    }

    private function replaceNameUniqueWithCountryNameUnique(): void
    {
        try {
            Schema::table('vendor_destination_ports', function (Blueprint $table) {
                $table->dropUnique(['name']);
            });
        } catch (\Throwable $e) {
            // Index may already be gone on some environments.
        }

        try {
            Schema::table('vendor_destination_ports', function (Blueprint $table) {
                $table->unique(['country_id', 'name']);
            });
        } catch (\Throwable $e) {
            // Composite unique may already exist.
        }
    }
}
