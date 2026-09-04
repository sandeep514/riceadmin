<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageToEquipmentProductVariantsTables extends Migration
{
    public function up()
    {
        if (Schema::hasTable('web_lab_equipment_product_variants')
            && ! Schema::hasColumn('web_lab_equipment_product_variants', 'image')) {
            Schema::table('web_lab_equipment_product_variants', function (Blueprint $table) {
                $table->string('image', 255)->nullable()->after('description');
            });
        }

        if (Schema::hasTable('web_machinery_equipment_product_variants')
            && ! Schema::hasColumn('web_machinery_equipment_product_variants', 'image')) {
            Schema::table('web_machinery_equipment_product_variants', function (Blueprint $table) {
                $table->string('image', 255)->nullable()->after('description');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('web_lab_equipment_product_variants')
            && Schema::hasColumn('web_lab_equipment_product_variants', 'image')) {
            Schema::table('web_lab_equipment_product_variants', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }

        if (Schema::hasTable('web_machinery_equipment_product_variants')
            && Schema::hasColumn('web_machinery_equipment_product_variants', 'image')) {
            Schema::table('web_machinery_equipment_product_variants', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }
}
