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
            $table->decimal('monthly_gst', 5, 2)->nullable()->after('monthly_discount_percentage');
            $table->decimal('quarterly_gst', 5, 2)->nullable()->after('quarterly_discount_percentage');
            $table->decimal('yearly_gst', 5, 2)->nullable()->after('yearly_discount_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_plan', function (Blueprint $table) {
            $table->dropColumn(['monthly_gst', 'quarterly_gst', 'yearly_gst']);
        });
    }
};
