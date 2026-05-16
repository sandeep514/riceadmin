<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'can_edit_by_admin')) {
            Schema::table('users', function (Blueprint $table) {
                // 1 = let SNTC approve search experience; 0 = user manages interests themselves
                $table->unsignedTinyInteger('can_edit_by_admin')->default(0)->after('user_from');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'can_edit_by_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('can_edit_by_admin');
            });
        }
    }
};
