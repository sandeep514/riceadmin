<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaidEmailsTable extends Migration
{
    public function up()
    {
        Schema::create('paidEmails', function (Blueprint $table) {
            $table->id();
            $table->string('email', 256);
            $table->string('companyName', 256)->nullable();
            $table->timestamp('dateFrom');
            $table->timestamp('dateTo');
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('paidEmails');
    }
}

