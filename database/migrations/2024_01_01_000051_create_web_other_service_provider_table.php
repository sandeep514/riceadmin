<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebOtherServiceProviderTable extends Migration
{
    public function up()
    {
        Schema::create('web_other_service_provider', function (Blueprint $table) {
            $table->id();
            $table->string('category', 255);
            $table->boolean('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_other_service_provider');
    }
}

