<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAppSettingsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table) {
                $table->id();
                $table->string('call_now_from', 5)->default('09:00');
                $table->string('call_now_to', 5)->default('18:00');
                $table->string('market_from', 5)->default('09:00');
                $table->string('market_to', 5)->default('18:00');
                // Temporary override from Live Prices Open/Closed/Hold until next schedule flip.
                $table->unsignedTinyInteger('market_override_status')->nullable();
                $table->timestamp('market_override_until')->nullable();
                $table->timestamps();
            });

            DB::table('app_settings')->insert([
                'call_now_from' => '09:00',
                'call_now_to' => '18:00',
                'market_from' => '09:00',
                'market_to' => '18:00',
                'market_override_status' => null,
                'market_override_until' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return;
        }

        // Table already created by an earlier draft of this migration.
        Schema::table('app_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('app_settings', 'market_override_status')) {
                $table->unsignedTinyInteger('market_override_status')->nullable()->after('market_to');
            }
            if (! Schema::hasColumn('app_settings', 'market_override_until')) {
                $table->timestamp('market_override_until')->nullable()->after('market_override_status');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('app_settings');
    }
}
