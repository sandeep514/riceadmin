<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('web_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('notify_date');
            $table->string('title', 500);
            $table->text('message');
            $table->unsignedInteger('role_id')->nullable();
            $table->unsignedInteger('category_id')->nullable();
            $table->string('audience_mode', 32)->default('all_category');
            $table->uuid('broadcast_group_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'notify_date']);
            $table->index('broadcast_group_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_notifications');
    }
}
