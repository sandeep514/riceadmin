<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebAccessTable extends Migration
{
    public function up()
    {
        Schema::create('web_access', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('route_name', 255);
            $table->boolean('can_create')->default(0);
            $table->boolean('can_read')->default(0);
            $table->boolean('can_update')->default(0);
            $table->boolean('can_delete')->default(0);
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->index(['role_id', 'category_id', 'plan_id', 'route_name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_access');
    }
}

