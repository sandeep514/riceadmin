<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionStatusToPackingTypesTable extends Migration
{
    public function up()
    {
        Schema::table('packing_types', function (Blueprint $table) {
            if (! Schema::hasColumn('packing_types', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (! Schema::hasColumn('packing_types', 'status')) {
                $table->tinyInteger('status')->default(1)->after('description');
                $table->index('status');
            }
        });
    }

    public function down()
    {
        Schema::table('packing_types', function (Blueprint $table) {
            if (Schema::hasColumn('packing_types', 'status')) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('packing_types', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
}
