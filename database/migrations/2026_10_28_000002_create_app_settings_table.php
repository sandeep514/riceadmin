<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAppSettingsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('app_settings')) {
            return;
        }

        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            // Call Now window (IST HH:MM)
            $table->string('call_now_from', 5)->default('09:00');
            $table->string('call_now_to', 5)->default('18:00');
            // Live market auto open window (IST HH:MM)
            $table->string('market_from', 5)->default('09:00');
            $table->string('market_to', 5)->default('18:00');
            // auto = API derives open/closed from market_from/to; manual = use live_price_current_status
            $table->string('market_mode', 16)->default('manual');
            $table->timestamps();
        });

        DB::table('app_settings')->insert([
            'call_now_from' => '09:00',
            'call_now_to' => '18:00',
            'market_from' => '09:00',
            'market_to' => '18:00',
            'market_mode' => 'manual',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('app_settings');
    }
}
