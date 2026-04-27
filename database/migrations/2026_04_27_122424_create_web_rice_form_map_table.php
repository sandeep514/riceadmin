<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('web_rice_form_map', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rice_name_id');
            $table->string('group_name');
            $table->json('form_ids');
            $table->timestamps();

            $table->foreign('rice_name_id')->references('id')->on('rice_names')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_rice_form_map');
    }
};
