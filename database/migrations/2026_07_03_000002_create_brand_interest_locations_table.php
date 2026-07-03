<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('brand_interest_locations')) {
            Schema::create('brand_interest_locations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('brand_interest_id');
                $table->integer('state_id');
                $table->integer('city_id');
                $table->string('state_name', 255)->nullable();
                $table->string('city_name', 255)->nullable();
                $table->unsignedTinyInteger('status')->default(1);
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->index('brand_interest_id', 'brand_interest_locations_interest_id_idx');
                $table->index(['state_id', 'city_id'], 'brand_interest_locations_state_city_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_interest_locations');
    }
};
