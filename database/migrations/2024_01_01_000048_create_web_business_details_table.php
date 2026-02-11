<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebBusinessDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('web_business_details', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('company_name', 256)->nullable();
            $table->text('product')->nullable();
            $table->string('contactPerson', 255)->nullable();
            $table->string('contactMobile', 256)->nullable();
            $table->string('designation', 256)->nullable();
            $table->text('address')->nullable();
            $table->string('registered_email', 256)->nullable();
            $table->string('phone', 256)->nullable();
            $table->string('selected_category', 256)->nullable();
            $table->string('locality', 256)->nullable();
            $table->string('landmark', 256)->nullable();
            $table->string('state', 256)->nullable();
            $table->string('city', 256)->nullable();
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_business_details');
    }
}

