<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebBrandVariantTable extends Migration
{
    public function up()
    {
        Schema::create('web_brand_variant', function (Blueprint $table) {
            $table->id();
            $table->string('variant', 255);
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('quality_id');
            $table->unsignedBigInteger('form_id');
            $table->string('grade', 255)->nullable();
            $table->string('packing', 255);
            $table->string('image', 255)->nullable();
            $table->string('cut_image', 255)->nullable();
            $table->boolean('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_brand_variant');
    }
}

