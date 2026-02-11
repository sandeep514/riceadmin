<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentRemindersTable extends Migration
{
    public function up()
    {
        Schema::create('payment_reminders', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('buyer');
            $table->unsignedBigInteger('seller');
            $table->string('invoice_number', 191);
            $table->double('amount');
            $table->double('rec_amount');
            $table->double('balance_amount');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('buyer');
            $table->index('seller');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_reminders');
    }
}

