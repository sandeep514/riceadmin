<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceProviderUserMapTable extends Migration
{
    public function up()
    {
        Schema::create('service_provider_user_map', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('type', 256);
            $table->string('key', 255);
            $table->string('value', 256);
            $table->text('remarks')->nullable();
            $table->integer('status')->default(1);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_provider_user_map');
    }
}

