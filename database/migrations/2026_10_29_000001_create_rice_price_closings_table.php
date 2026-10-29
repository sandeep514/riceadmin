<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRicePriceClosingsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('rice_price_closings')) {
            return;
        }

        Schema::create('rice_price_closings', function (Blueprint $table) {
            $table->id();
            $table->date('close_date');
            $table->string('reason', 500);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique('close_date', 'rice_price_closings_close_date_unique');
            $table->index('close_date', 'rice_price_closings_close_date_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rice_price_closings');
    }
}
