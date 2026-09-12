<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInputTypeToVendorContainerParticularsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('vendor_container_particulars')) {
            return;
        }

        if (! Schema::hasColumn('vendor_container_particulars', 'input_type')) {
            Schema::table('vendor_container_particulars', function (Blueprint $table) {
                $table->string('input_type', 50)->default('number')->after('particular');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('vendor_container_particulars')
            && Schema::hasColumn('vendor_container_particulars', 'input_type')
        ) {
            Schema::table('vendor_container_particulars', function (Blueprint $table) {
                $table->dropColumn('input_type');
            });
        }
    }
}
