<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtherValuesToVendorPackagingProducts extends Migration
{
    public function up()
    {
        if (Schema::hasTable('web_rice_bag_products')
            && ! Schema::hasColumn('web_rice_bag_products', 'other_type_value')
        ) {
            Schema::table('web_rice_bag_products', function (Blueprint $table) {
                $table->string('other_type_value', 255)->nullable()->after('bag_type_id');
            });
        }

        if (Schema::hasTable('web_rice_bag_product_packing_sizes')
            && ! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'other_size_value')
        ) {
            Schema::table('web_rice_bag_product_packing_sizes', function (Blueprint $table) {
                $table->string('other_size_value', 255)->nullable()->after('packing_size');
            });
        }

        if (Schema::hasTable('web_cartoon_products')
            && ! Schema::hasColumn('web_cartoon_products', 'other_type_value')
        ) {
            Schema::table('web_cartoon_products', function (Blueprint $table) {
                $table->string('other_type_value', 255)->nullable()->after('cartoon_type_id');
            });
        }

        if (Schema::hasTable('web_cartoon_product_variants')
            && ! Schema::hasColumn('web_cartoon_product_variants', 'other_size_value')
        ) {
            Schema::table('web_cartoon_product_variants', function (Blueprint $table) {
                $table->string('other_size_value', 255)->nullable()->after('packing_size');
            });
        }

        if (Schema::hasTable('web_cylinder_products')
            && ! Schema::hasColumn('web_cylinder_products', 'other_type_value')
        ) {
            Schema::table('web_cylinder_products', function (Blueprint $table) {
                $table->string('other_type_value', 255)->nullable()->after('cylinder_type_id');
            });
        }

        if (Schema::hasTable('web_cylinder_product_variants')
            && ! Schema::hasColumn('web_cylinder_product_variants', 'other_size_value')
        ) {
            Schema::table('web_cylinder_product_variants', function (Blueprint $table) {
                $table->string('other_size_value', 255)->nullable()->after('packing_size');
            });
        }
    }

    public function down()
    {
        $drops = [
            'web_rice_bag_products' => 'other_type_value',
            'web_rice_bag_product_packing_sizes' => 'other_size_value',
            'web_cartoon_products' => 'other_type_value',
            'web_cartoon_product_variants' => 'other_size_value',
            'web_cylinder_products' => 'other_type_value',
            'web_cylinder_product_variants' => 'other_size_value',
        ];

        foreach ($drops as $table => $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                Schema::table($table, function (Blueprint $blueprint) use ($column) {
                    $blueprint->dropColumn($column);
                });
            }
        }
    }
}
