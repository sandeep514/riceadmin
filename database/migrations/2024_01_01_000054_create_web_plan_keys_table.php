<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebPlanKeysTable extends Migration
{
    public function up()
    {
        Schema::create('web_plan_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key', 255);
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_plan_keys');
    }
}

