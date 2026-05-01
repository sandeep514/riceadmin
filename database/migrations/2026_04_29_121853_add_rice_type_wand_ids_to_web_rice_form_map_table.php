<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('web_rice_form_map', function (Blueprint $table) {
            $table->string('rice_type')->nullable()->after('id'); // 'basmati' or 'non-basmati'
            $table->json('wand_ids')->nullable()->after('form_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_rice_form_map', function (Blueprint $table) {
            $table->dropColumn(['rice_type', 'wand_ids']);
        });
    }
};
