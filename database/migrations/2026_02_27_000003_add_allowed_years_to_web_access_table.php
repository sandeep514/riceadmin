<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllowedYearsToWebAccessTable extends Migration
{
    public function up()
    {
        Schema::table('web_access', function (Blueprint $table) {
            $table->json('allowed_years')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('web_access', function (Blueprint $table) {
            $table->dropColumn('allowed_years');
        });
    }
}

