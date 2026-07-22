<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsClearedToNotificationTables extends Migration
{
    public function up()
    {
        Schema::table('web_notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('web_notifications', 'is_cleared')) {
                $table->unsignedTinyInteger('is_cleared')->default(0)->after('read_at');
                $table->index(['user_id', 'is_cleared'], 'web_notifications_user_cleared_index');
            }
        });

        Schema::table('notification', function (Blueprint $table) {
            if (! Schema::hasColumn('notification', 'is_cleared')) {
                $table->unsignedTinyInteger('is_cleared')->default(0)->after('status');
                $table->index(['user_id', 'is_cleared'], 'notification_user_cleared_index');
            }
        });
    }

    public function down()
    {
        Schema::table('web_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('web_notifications', 'is_cleared')) {
                $table->dropIndex('web_notifications_user_cleared_index');
                $table->dropColumn('is_cleared');
            }
        });

        Schema::table('notification', function (Blueprint $table) {
            if (Schema::hasColumn('notification', 'is_cleared')) {
                $table->dropIndex('notification_user_cleared_index');
                $table->dropColumn('is_cleared');
            }
        });
    }
}
