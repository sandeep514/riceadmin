<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClearingAgentPortMastersTables extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('vendor_icd_locations')) {
            Schema::create('vendor_icd_locations', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->unique('name');
                $table->index('status');
            });
        }

        if (! Schema::hasTable('vendor_indian_ports')) {
            Schema::create('vendor_indian_ports', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->unique('name');
                $table->index('status');
            });
        }

        if (! Schema::hasTable('vendor_destination_ports')) {
            Schema::create('vendor_destination_ports', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->unique('name');
                $table->index('status');
            });
        }

        if (Schema::hasTable('web_cleaning_agent_products')) {
            Schema::table('web_cleaning_agent_products', function (Blueprint $table) {
                if (! Schema::hasColumn('web_cleaning_agent_products', 'icd_location_id')) {
                    $table->unsignedBigInteger('icd_location_id')->nullable()->after('port_type')->index();
                }
                if (! Schema::hasColumn('web_cleaning_agent_products', 'indian_port_id')) {
                    $table->unsignedBigInteger('indian_port_id')->nullable()->after('icd_location')->index();
                }
                if (! Schema::hasColumn('web_cleaning_agent_products', 'destination_port_id')) {
                    $table->unsignedBigInteger('destination_port_id')->nullable()->after('port_location')->index();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('web_cleaning_agent_products')) {
            Schema::table('web_cleaning_agent_products', function (Blueprint $table) {
                foreach (['icd_location_id', 'indian_port_id', 'destination_port_id'] as $col) {
                    if (Schema::hasColumn('web_cleaning_agent_products', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        Schema::dropIfExists('vendor_destination_ports');
        Schema::dropIfExists('vendor_indian_ports');
        Schema::dropIfExists('vendor_icd_locations');
    }
}
