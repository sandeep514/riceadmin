<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleIdCategoryIdToTradeQueryMilestone3Table extends Migration
{
    public function up()
    {
        Schema::table('trade_query_milestone3', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('status');
            $table->unsignedBigInteger('category_id')->nullable()->after('role_id');
        });
    }

    public function down()
    {
        Schema::table('trade_query_milestone3', function (Blueprint $table) {
            $table->dropColumn(['role_id', 'category_id']);
        });
    }
}
