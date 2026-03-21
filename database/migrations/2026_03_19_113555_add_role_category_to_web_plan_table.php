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
        Schema::table('web_plan', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('title');
            $table->unsignedBigInteger('category_id')->nullable()->after('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_plan', function (Blueprint $table) {
            $table->dropColumn(['role_id', 'category_id']);
        });
    }
};
