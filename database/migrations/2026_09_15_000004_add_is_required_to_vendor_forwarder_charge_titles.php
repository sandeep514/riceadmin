<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddIsRequiredToVendorForwarderChargeTitles extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('vendor_forwarder_charge_titles')) {
            return;
        }

        if (! Schema::hasColumn('vendor_forwarder_charge_titles', 'is_required')) {
            Schema::table('vendor_forwarder_charge_titles', function (Blueprint $table) {
                $table->tinyInteger('is_required')->default(1)->after('status');
            });
        }

        DB::table('vendor_forwarder_charge_titles')->update(['is_required' => 1]);
    }

    public function down()
    {
        if (Schema::hasTable('vendor_forwarder_charge_titles')
            && Schema::hasColumn('vendor_forwarder_charge_titles', 'is_required')) {
            Schema::table('vendor_forwarder_charge_titles', function (Blueprint $table) {
                $table->dropColumn('is_required');
            });
        }
    }
}
