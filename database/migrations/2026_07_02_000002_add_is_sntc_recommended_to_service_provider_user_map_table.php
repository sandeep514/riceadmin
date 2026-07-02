<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('service_provider_user_map', 'is_sntc_recommended')) {
            Schema::table('service_provider_user_map', function (Blueprint $table) {
                $table->unsignedTinyInteger('is_sntc_recommended')->default(0)->before('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('service_provider_user_map', 'is_sntc_recommended')) {
            Schema::table('service_provider_user_map', function (Blueprint $table) {
                $table->dropColumn('is_sntc_recommended');
            });
        }
    }
};
