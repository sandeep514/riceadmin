<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebUserSubscriptionTable extends Migration
{
    public function up()
    {
        Schema::create('web_user_subscription', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('plan_id');
            $table->string('payment_id', 255);
            $table->string('order_id', 256);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->string('subscription_type', 255);
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_user_subscription');
    }
}

