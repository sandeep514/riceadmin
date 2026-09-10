<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateClearingAgentPortTypeAndContainerSizeMasters extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('vendor_port_types')) {
            Schema::create('vendor_port_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->unique('name');
                $table->index('status');
            });
        }

        if (! Schema::hasTable('vendor_container_sizes')) {
            Schema::create('vendor_container_sizes', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('size');
                $table->string('label', 255)->nullable();
                $table->text('description')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->unique('size');
                $table->index('status');
            });
        }

        $now = now();
        foreach (['ICD', 'Sea Port'] as $name) {
            if (Schema::hasTable('vendor_port_types')
                && ! DB::table('vendor_port_types')->where('name', $name)->exists()) {
                DB::table('vendor_port_types')->insert([
                    'name' => $name,
                    'description' => null,
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        foreach ([40 => '40 FT', 50 => '50 FT'] as $size => $label) {
            if (Schema::hasTable('vendor_container_sizes')
                && ! DB::table('vendor_container_sizes')->where('size', $size)->exists()) {
                DB::table('vendor_container_sizes')->insert([
                    'size' => $size,
                    'label' => $label,
                    'description' => null,
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if (Schema::hasTable('web_cleaning_agent_products')) {
            Schema::table('web_cleaning_agent_products', function (Blueprint $table) {
                if (! Schema::hasColumn('web_cleaning_agent_products', 'port_type_id')) {
                    $table->unsignedBigInteger('port_type_id')->nullable()->after('container_size')->index();
                }
                if (! Schema::hasColumn('web_cleaning_agent_products', 'container_size_id')) {
                    $table->unsignedBigInteger('container_size_id')->nullable()->after('user_id')->index();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('web_cleaning_agent_products')) {
            Schema::table('web_cleaning_agent_products', function (Blueprint $table) {
                foreach (['port_type_id', 'container_size_id'] as $col) {
                    if (Schema::hasColumn('web_cleaning_agent_products', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        Schema::dropIfExists('vendor_container_sizes');
        Schema::dropIfExists('vendor_port_types');
    }
}
