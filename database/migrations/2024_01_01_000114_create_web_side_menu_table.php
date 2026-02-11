<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebSideMenuTable extends Migration
{
    public function up()
    {
        Schema::create('web_side_menu', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('sub_title', 255)->nullable();
            $table->string('create_url', 255)->nullable();
            $table->string('read_url', 255)->nullable();
            $table->string('update_url', 255)->nullable();
            $table->string('delete_url', 255)->nullable();
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_side_menu');
    }
}

