<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avg_length_maps', function (Blueprint $table) {
            $table->id();
            $table->string('quality_type', 32)->comment('basmati / non-basmati');
            $table->unsignedBigInteger('rice_name_id');
            $table->unsignedBigInteger('form_id');
            $table->unsignedBigInteger('wand_id');
            $table->decimal('avg_length', 10, 2);
            $table->timestamps();

            $table->unique(['quality_type', 'rice_name_id', 'form_id', 'wand_id'], 'avg_length_maps_unique');
            $table->foreign('rice_name_id')->references('id')->on('rice_names')->onDelete('cascade');
            $table->foreign('form_id')->references('id')->on('rice_form_milestone3')->onDelete('cascade');
            $table->foreign('wand_id')->references('id')->on('wand')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avg_length_maps');
    }
};
