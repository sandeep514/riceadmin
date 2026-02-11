<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebVendorcategoryTable extends Migration
{
    public function up()
    {
        Schema::create('web_vendorcategory', function (Blueprint $table) {
            $table->id();
            $table->string('name', 256);
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_vendorcategory');
    }
}

