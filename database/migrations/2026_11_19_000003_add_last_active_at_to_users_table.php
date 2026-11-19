<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddLastActiveAtToUsersTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'last_active_at')) {
                $table->timestamp('last_active_at')->nullable()->after('last_login_platform');
            }
            if (! Schema::hasColumn('users', 'last_active_platform')) {
                $table->string('last_active_platform', 20)->nullable()->after('last_active_at');
            }
        });

        // Seed from last login so the new "recently active" list is not empty.
        if (Schema::hasColumn('users', 'last_active_at') && Schema::hasColumn('users', 'last_login_at')) {
            $sql = 'UPDATE users SET last_active_at = last_login_at';
            if (Schema::hasColumn('users', 'last_active_platform')
                && Schema::hasColumn('users', 'last_login_platform')) {
                $sql .= ', last_active_platform = last_login_platform';
            }
            $sql .= ' WHERE last_active_at IS NULL AND last_login_at IS NOT NULL';
            DB::statement($sql);
        }
    }

    public function down()
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_active_platform')) {
                $table->dropColumn('last_active_platform');
            }
            if (Schema::hasColumn('users', 'last_active_at')) {
                $table->dropColumn('last_active_at');
            }
        });
    }
}
