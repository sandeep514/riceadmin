<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('payment_type', 256)->default('INR');
            $table->string('amount', 256)->nullable();
            $table->integer('user_id');
            $table->string('transaction_id', 100);
            $table->integer('plan_id');
            $table->integer('sub_plan_id');
            $table->text('plan_name');
            $table->text('start_date');
            $table->text('end_date');
            $table->text('sub_plan_name');
            $table->float('sub_plan_price');
            $table->integer('status')->default(1);
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index('user_id');
            $table->index('transaction_id');
            $table->index('plan_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}

