<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('web_business_details', 'is_sntc_recommended')) {
            Schema::table('web_business_details', function (Blueprint $table) {
                $table->unsignedTinyInteger('is_sntc_recommended')->default(0)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('web_business_details', 'is_sntc_recommended')) {
            Schema::table('web_business_details', function (Blueprint $table) {
                $table->dropColumn('is_sntc_recommended');
            });
        }
    }
};
