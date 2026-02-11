<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebPlanTable extends Migration
{
    public function up()
    {
        Schema::create('web_plan', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->longText('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->integer('is_INR')->default(0);
            $table->integer('is_USD')->default(0);
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_plan');
    }
}

