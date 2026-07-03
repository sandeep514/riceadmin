<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('web_business_details', 'is_active_listing')) {
            Schema::table('web_business_details', function (Blueprint $table) {
                $table->unsignedTinyInteger('is_active_listing')->default(0)->after('is_sntc_recommended');
            });

            // Keep existing vendors visible in listings until admin changes them.
            DB::table('web_business_details')->update(['is_active_listing' => 1]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('web_business_details', 'is_active_listing')) {
            Schema::table('web_business_details', function (Blueprint $table) {
                $table->dropColumn('is_active_listing');
            });
        }
    }
};
