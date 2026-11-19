<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Map rice quality + form → one or more wand grades
 * (e.g. 1121 + Steam → 4 Wands, 2 Short Wands).
 */
class CreateQualityGradeMapsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('quality_grade_maps')) {
            return;
        }

        Schema::create('quality_grade_maps', function (Blueprint $table) {
            $table->id();
            $table->string('quality_type', 32); // basmati | non-basmati
            $table->unsignedInteger('rice_name_id');
            $table->unsignedInteger('form_id');
            $table->json('wand_ids');
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedInteger('order')->nullable();
            $table->timestamps();

            $table->unique(
                ['quality_type', 'rice_name_id', 'form_id'],
                'quality_grade_maps_unique'
            );
            $table->index(['rice_name_id', 'form_id'], 'quality_grade_maps_quality_form_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('quality_grade_maps');
    }
}
