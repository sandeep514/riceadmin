<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryRoleMapTable extends Migration
{
    public function up()
    {
        Schema::create('category_role_map', function (Blueprint $table) {
            $table->id();
            $table->integer('role')->nullable();
            $table->integer('category')->nullable();
            $table->integer('status')->default(1);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('category_role_map');
    }
}

