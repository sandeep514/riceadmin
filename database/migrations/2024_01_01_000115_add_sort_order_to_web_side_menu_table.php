<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortOrderToWebSideMenuTable extends Migration
{
    public function up()
    {
        Schema::table('web_side_menu', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('status');
        });
    }

    public function down()
    {
        Schema::table('web_side_menu', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
}

