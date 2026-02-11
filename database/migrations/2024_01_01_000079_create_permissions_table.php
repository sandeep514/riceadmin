<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('module_id');
            $table->unsignedBigInteger('designation')->nullable();
            $table->text('route_name')->nullable();
            $table->string('action', 191);
            $table->boolean('status')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('role_id');
            $table->index('module_id');
            $table->index('designation');
        });
    }

    public function down()
    {
        Schema::dropIfExists('permissions');
    }
}

