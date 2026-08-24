<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reshape parent product columns to:
 * user_id, bag_type_id, specification, description,
 * additional_information, packing_form_id, packing_form
 * and drop packing_form from packing size rows.
 */
class ReshapeWebRiceBagProductsColumns extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('web_rice_bag_products')) {
            return;
        }

        Schema::table('web_rice_bag_products', function (Blueprint $table) {
            if (! Schema::hasColumn('web_rice_bag_products', 'bag_type_id')) {
                $table->unsignedBigInteger('bag_type_id')->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('web_rice_bag_products', 'specification')) {
                $table->text('specification')->nullable()->after('bag_type_id');
            }
            if (! Schema::hasColumn('web_rice_bag_products', 'additional_information')) {
                $table->text('additional_information')->nullable()->after('description');
            }
            if (! Schema::hasColumn('web_rice_bag_products', 'packing_form_id')) {
                $table->unsignedBigInteger('packing_form_id')->nullable()->after('additional_information');
            }
            if (! Schema::hasColumn('web_rice_bag_products', 'packing_form')) {
                $table->string('packing_form', 64)->nullable()->after('packing_form_id');
            }
        });

        $legacyColumns = [
            'product_name',
            'rice_name_id',
            'rice_form_id',
            'rice_form',
            'bag_color',
            'print_type',
        ];
        $toDrop = [];
        foreach ($legacyColumns as $column) {
            if (Schema::hasColumn('web_rice_bag_products', $column)) {
                $toDrop[] = $column;
            }
        }
        if (! empty($toDrop)) {
            Schema::table('web_rice_bag_products', function (Blueprint $table) use ($toDrop) {
                $table->dropColumn($toDrop);
            });
        }

        if (Schema::hasTable('web_rice_bag_product_packing_sizes')
            && Schema::hasColumn('web_rice_bag_product_packing_sizes', 'packing_form')
        ) {
            Schema::table('web_rice_bag_product_packing_sizes', function (Blueprint $table) {
                $table->dropColumn('packing_form');
            });
        }
    }

    public function down()
    {
        if (! Schema::hasTable('web_rice_bag_products')) {
            return;
        }

        Schema::table('web_rice_bag_products', function (Blueprint $table) {
            if (! Schema::hasColumn('web_rice_bag_products', 'product_name')) {
                $table->string('product_name', 255)->nullable();
            }
            if (! Schema::hasColumn('web_rice_bag_products', 'rice_name_id')) {
                $table->unsignedBigInteger('rice_name_id')->nullable();
            }
            if (! Schema::hasColumn('web_rice_bag_products', 'rice_form_id')) {
                $table->unsignedBigInteger('rice_form_id')->nullable();
            }
            if (! Schema::hasColumn('web_rice_bag_products', 'rice_form')) {
                $table->string('rice_form', 255)->nullable();
            }
            if (! Schema::hasColumn('web_rice_bag_products', 'bag_color')) {
                $table->string('bag_color', 64)->nullable();
            }
            if (! Schema::hasColumn('web_rice_bag_products', 'print_type')) {
                $table->string('print_type', 64)->nullable();
            }
        });

        $newColumns = [
            'bag_type_id',
            'specification',
            'additional_information',
            'packing_form_id',
            'packing_form',
        ];
        $toDrop = [];
        foreach ($newColumns as $column) {
            if (Schema::hasColumn('web_rice_bag_products', $column)) {
                $toDrop[] = $column;
            }
        }
        if (! empty($toDrop)) {
            Schema::table('web_rice_bag_products', function (Blueprint $table) use ($toDrop) {
                $table->dropColumn($toDrop);
            });
        }

        if (Schema::hasTable('web_rice_bag_product_packing_sizes')
            && ! Schema::hasColumn('web_rice_bag_product_packing_sizes', 'packing_form')
        ) {
            Schema::table('web_rice_bag_product_packing_sizes', function (Blueprint $table) {
                $table->string('packing_form', 64)->nullable()->after('packing');
            });
        }
    }
}
