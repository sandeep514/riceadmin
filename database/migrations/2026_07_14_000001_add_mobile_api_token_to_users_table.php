<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'mobile_api_token')) {
                $table->string('mobile_api_token', 255)->nullable()->after('api_token');
                $table->index('mobile_api_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'mobile_api_token')) {
                $table->dropIndex(['mobile_api_token']);
                $table->dropColumn('mobile_api_token');
            }
        });
    }
};
