<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugToWebSideMenuTable extends Migration
{
    public function up()
    {
        Schema::table('web_side_menu', function (Blueprint $table) {
            $table->string('slug', 255)->nullable()->after('sub_title');
        });
    }

    public function down()
    {
        Schema::table('web_side_menu', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
}

