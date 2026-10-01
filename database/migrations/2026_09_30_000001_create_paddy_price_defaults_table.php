<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePaddyPriceDefaultsTable extends Migration
{
    public function up()
    {
        Schema::create('paddy_price_defaults', function (Blueprint $table) {
            $table->id();
            // Which frontend "View by" button is active by default: mandi (Mandis) or crop (Crops).
            $table->string('default_view_by', 10)->default('mandi');
            $table->timestamps();
        });

        DB::table('paddy_price_defaults')->insert([
            'id' => 1,
            'default_view_by' => 'mandi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('paddy_price_defaults');
    }
}
