<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToPaddyQualitiesTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('paddy_qualities', 'type')) {
            Schema::table('paddy_qualities', function (Blueprint $table) {
                $table->string('type', 191)->nullable()->after('id');
            });
        }

        // Clean up earlier rice_type_id FK-style column if present
        if (Schema::hasColumn('paddy_qualities', 'rice_type_id')) {
            Schema::table('paddy_qualities', function (Blueprint $table) {
                $table->dropColumn('rice_type_id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('paddy_qualities', 'type')) {
            Schema::table('paddy_qualities', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
}
