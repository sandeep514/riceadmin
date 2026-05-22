<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rice_form_parent_maps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_form_id');
            $table->json('child_form_ids');
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique('parent_form_id');
            $table->foreign('parent_form_id')
                ->references('id')
                ->on('rice_form_milestone3')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rice_form_parent_maps');
    }
};
