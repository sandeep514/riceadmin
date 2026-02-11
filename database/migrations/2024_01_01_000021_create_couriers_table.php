<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouriersTable extends Migration
{
    public function up()
    {
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->longText('samples');
            $table->unsignedBigInteger('sent_via');
            $table->string('details', 191);
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('couriers');
    }
}

