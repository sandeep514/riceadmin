<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebPersonalDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('web_personal_details', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('firstname', 256)->nullable();
            $table->string('lastname', 256)->nullable();
            $table->string('email', 256)->nullable();
            $table->string('phone_number', 256)->nullable();
            $table->string('state', 255)->nullable();
            $table->string('district', 256)->nullable();
            $table->string('address', 256)->nullable();
            $table->string('farmer_unique_id', 256)->nullable();
            $table->string('pan_card', 256)->nullable();
            $table->string('avatar', 256)->nullable();
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_personal_details');
    }
}

