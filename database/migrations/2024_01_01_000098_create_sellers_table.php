<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellersTable extends Migration
{
    public function up()
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('company_name', 191);
            $table->string('contact_person', 191);
            $table->longText('email_ids')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sellers');
    }
}

