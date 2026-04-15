<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLivePriceEventsTable extends Migration
{
    public function up()
    {
        Schema::create('live_price_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('quality_type_id')->nullable();
            $table->unsignedBigInteger('quality_id')->nullable();
            $table->unsignedBigInteger('quality_form_id')->nullable();
            $table->date('event_date');
            $table->text('note');
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();

            $table->index('event_date');
            $table->index('quality_type_id');
            $table->index('quality_id');
            $table->index('quality_form_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('live_price_events');
    }
}
