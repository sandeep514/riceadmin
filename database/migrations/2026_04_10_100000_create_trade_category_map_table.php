<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradeCategoryMapTable extends Migration
{
    public function up()
    {
        Schema::create('trade_category_map', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trade_id');
            $table->unsignedBigInteger('category_id');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique(['trade_id', 'category_id'], 'trade_category_unique');
            $table->index('trade_id');
            $table->index('category_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('trade_category_map');
    }
}
