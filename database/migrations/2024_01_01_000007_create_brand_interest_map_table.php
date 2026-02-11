<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandInterestMapTable extends Migration
{
    public function up()
    {
        Schema::create('brand_interest_map', function (Blueprint $table) {
            $table->id();
            $table->integer('brand_interest_id');
            $table->string('already_working_with_brand_name', 255)->nullable();
            $table->boolean('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('brand_interest_map');
    }
}

