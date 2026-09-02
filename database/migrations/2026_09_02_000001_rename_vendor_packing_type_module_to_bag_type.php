<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RenameVendorPackingTypeModuleToBagType extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('modules')) {
            return;
        }

        DB::table('modules')
            ->where('slug', 'vendor_packing_type')
            ->update([
                'name' => 'Bag Type',
                'slug' => 'bag_type',
                'icon' => 'fa-shopping-bag',
                'updated_at' => now(),
            ]);
    }

    public function down()
    {
        if (! Schema::hasTable('modules')) {
            return;
        }

        DB::table('modules')
            ->where('slug', 'bag_type')
            ->update([
                'name' => 'Vendor Packing Type',
                'slug' => 'vendor_packing_type',
                'icon' => 'fa-tags',
                'updated_at' => now(),
            ]);
    }
}
