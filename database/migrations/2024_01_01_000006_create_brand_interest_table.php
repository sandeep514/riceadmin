<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandInterestTable extends Migration
{
    public function up()
    {
        Schema::create('brand_interest', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('brand_id');
            $table->string('contact_person_name', 255);
            $table->string('contact_person_number', 20);
            $table->string('basmati_monthly', 100)->nullable();
            $table->string('non_basmati_monthly', 100)->nullable();
            $table->boolean('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('brand_interest');
    }
}

