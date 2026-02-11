<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebUserAttachmentTable extends Migration
{
    public function up()
    {
        Schema::create('web_user_attachment', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('farmer_file', 255)->nullable();
            $table->string('panCard', 256)->nullable();
            $table->string('gstCard', 256)->nullable();
            $table->string('fssaiCard', 256)->nullable();
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_user_attachment');
    }
}

