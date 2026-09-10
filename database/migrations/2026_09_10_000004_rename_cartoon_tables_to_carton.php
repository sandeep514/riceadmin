<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RenameCartoonTablesToCarton extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cartoon_types') && ! Schema::hasTable('carton_types')) {
            Schema::rename('cartoon_types', 'carton_types');
        }

        if (Schema::hasTable('web_cartoon_products') && ! Schema::hasTable('web_carton_products')) {
            Schema::rename('web_cartoon_products', 'web_carton_products');
        }

        if (Schema::hasTable('web_cartoon_product_variants') && ! Schema::hasTable('web_carton_product_variants')) {
            Schema::rename('web_cartoon_product_variants', 'web_carton_product_variants');
        }

        if (Schema::hasTable('web_carton_products')
            && Schema::hasColumn('web_carton_products', 'cartoon_type_id')
            && ! Schema::hasColumn('web_carton_products', 'carton_type_id')
        ) {
            DB::statement('ALTER TABLE `web_carton_products` CHANGE `cartoon_type_id` `carton_type_id` BIGINT UNSIGNED NULL');
        }

        $fromUploads = public_path('uploads/cartoon-products');
        $toUploads = public_path('uploads/carton-products');
        if (is_dir($fromUploads) && ! is_dir($toUploads)) {
            @rename($fromUploads, $toUploads);
        }
    }

    public function down()
    {
        if (Schema::hasTable('web_carton_products')
            && Schema::hasColumn('web_carton_products', 'carton_type_id')
            && ! Schema::hasColumn('web_carton_products', 'cartoon_type_id')
        ) {
            DB::statement('ALTER TABLE `web_carton_products` CHANGE `carton_type_id` `cartoon_type_id` BIGINT UNSIGNED NULL');
        }

        if (Schema::hasTable('web_carton_product_variants') && ! Schema::hasTable('web_cartoon_product_variants')) {
            Schema::rename('web_carton_product_variants', 'web_cartoon_product_variants');
        }

        if (Schema::hasTable('web_carton_products') && ! Schema::hasTable('web_cartoon_products')) {
            Schema::rename('web_carton_products', 'web_cartoon_products');
        }

        if (Schema::hasTable('carton_types') && ! Schema::hasTable('cartoon_types')) {
            Schema::rename('carton_types', 'cartoon_types');
        }

        $fromUploads = public_path('uploads/carton-products');
        $toUploads = public_path('uploads/cartoon-products');
        if (is_dir($fromUploads) && ! is_dir($toUploads)) {
            @rename($fromUploads, $toUploads);
        }
    }
}
