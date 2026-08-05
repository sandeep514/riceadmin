<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRiceTypeIdToPaddyQualitiesTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('paddy_qualities', 'rice_type_id')) {
            Schema::table('paddy_qualities', function (Blueprint $table) {
                $table->unsignedBigInteger('rice_type_id')->nullable()->after('id');
                $table->index('rice_type_id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('paddy_qualities', 'rice_type_id')) {
            Schema::table('paddy_qualities', function (Blueprint $table) {
                $table->dropIndex(['rice_type_id']);
                $table->dropColumn('rice_type_id');
            });
        }
    }
}
