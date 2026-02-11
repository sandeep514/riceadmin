<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebPlanKeysMapTable extends Migration
{
    public function up()
    {
        Schema::create('web_plan_keys_map', function (Blueprint $table) {
            $table->id();
            $table->integer('plan_id');
            $table->integer('key_id');
            $table->integer('value')->nullable();
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_plan_keys_map');
    }
}

