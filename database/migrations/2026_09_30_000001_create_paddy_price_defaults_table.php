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
            $table->unsignedBigInteger('default_mandi_id')->nullable();
            $table->unsignedBigInteger('default_quality_id')->nullable();
            $table->timestamps();
        });

        DB::table('paddy_price_defaults')->insert([
            'id' => 1,
            'default_mandi_id' => null,
            'default_quality_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('paddy_price_defaults');
    }
}
