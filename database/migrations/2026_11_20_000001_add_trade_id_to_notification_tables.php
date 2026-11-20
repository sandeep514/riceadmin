<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTradeIdToNotificationTables extends Migration
{
    public function up()
    {
        if (Schema::hasTable('web_notifications') && ! Schema::hasColumn('web_notifications', 'trade_id')) {
            Schema::table('web_notifications', function (Blueprint $table) {
                $table->unsignedBigInteger('trade_id')->nullable()->after('category_id');
                $table->index('trade_id');
            });
        }

        if (Schema::hasTable('notification') && ! Schema::hasColumn('notification', 'trade_id')) {
            Schema::table('notification', function (Blueprint $table) {
                $table->unsignedBigInteger('trade_id')->nullable()->after('userAppType');
                $table->index('trade_id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('web_notifications') && Schema::hasColumn('web_notifications', 'trade_id')) {
            Schema::table('web_notifications', function (Blueprint $table) {
                $table->dropIndex(['trade_id']);
                $table->dropColumn('trade_id');
            });
        }

        if (Schema::hasTable('notification') && Schema::hasColumn('notification', 'trade_id')) {
            Schema::table('notification', function (Blueprint $table) {
                $table->dropIndex(['trade_id']);
                $table->dropColumn('trade_id');
            });
        }
    }
}
