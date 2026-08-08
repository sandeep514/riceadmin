<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaddyTradesTable extends Migration
{
    public function up()
    {
        Schema::create('paddy_trades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paddy_sell_query_id')->nullable();
            $table->string('category', 50)->nullable(); // basmati | non-basmati
            $table->unsignedBigInteger('quality')->nullable();
            $table->string('quality_name', 255)->nullable();
            $table->string('hand_combined', 100)->nullable();
            $table->unsignedBigInteger('packing_id')->nullable();
            $table->string('packing', 255)->nullable(); // denormalized label
            $table->string('contact_number', 50)->nullable();
            $table->string('contact_person', 255)->nullable();
            $table->string('image', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('quantity', 100)->nullable();
            $table->string('rate', 100)->nullable();
            $table->string('valid_days', 255)->nullable();
            $table->string('type', 50)->default('web');
            $table->unsignedBigInteger('user_id')->nullable(); // original seller user
            $table->text('remarks')->nullable();
            $table->unsignedTinyInteger('status')->default(1); // 1 Active, 4 In-Process, 12 Hold, 3 Sold
            $table->unsignedTinyInteger('is_new')->default(0); // 1 yes, 0 no
            $table->string('sold_at_amount', 100)->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // admin user id
            $table->timestamps();

            $table->index('paddy_sell_query_id');
            $table->index('category');
            $table->index('quality');
            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('paddy_trades');
    }
}
