<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaddySellQueriesTable extends Migration
{
    public function up()
    {
        Schema::create('paddy_sell_queries', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50)->nullable(); // basmati | non-basmati
            $table->unsignedBigInteger('quality')->nullable(); // paddy_qualities.id
            $table->string('quality_name', 255)->nullable();
            $table->string('hand_combined', 100)->nullable();
            $table->unsignedBigInteger('packing_id')->nullable();
            $table->string('packing', 255)->nullable(); // denormalized label from packing master
            $table->string('contact_number', 50)->nullable();
            $table->string('contact_person', 255)->nullable();
            $table->string('image', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('quantity', 100)->nullable();
            $table->string('rate', 100)->nullable();
            $table->string('valid_days', 255)->nullable();
            $table->string('type', 50)->default('web'); // web | app
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedTinyInteger('status')->default(1); // 1 pending, 0 closed
            $table->timestamps();

            $table->index('category');
            $table->index('quality');
            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('paddy_sell_queries');
    }
}
