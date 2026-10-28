<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToRiceFormsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('rice_forms')) {
            return;
        }

        if (! Schema::hasColumn('rice_forms', 'description')) {
            Schema::table('rice_forms', function (Blueprint $table) {
                $table->text('description')->nullable()->after('form_name');
            });
        }
    }

    public function down()
    {
        if (! Schema::hasTable('rice_forms')) {
            return;
        }

        if (Schema::hasColumn('rice_forms', 'description')) {
            Schema::table('rice_forms', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
}
