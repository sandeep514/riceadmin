<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHotdealsTable extends Migration
{
    public function up()
    {
        Schema::create('hotdeals', function (Blueprint $table) {
            $table->id();
            $table->string('title', 256);
            $table->string('quality', 256);
            $table->string('fob', 256);
            $table->string('qty', 256);
            $table->string('packing', 256);
            $table->text('message');
            $table->string('validDate', 256);
            $table->integer('status')->default(1)->comment('{0 : taken, 1: active(default) ,2: sold  }');
            $table->string('attachment1', 256)->nullable();
            $table->string('attachment2', 256)->nullable();
            $table->string('length', 256)->nullable();
            $table->string('purity', 256)->nullable();
            $table->string('moisture', 256)->nullable();
            $table->string('broken', 256)->nullable();
            $table->string('kett', 256)->nullable();
            $table->string('dd', 256)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hotdeals');
    }
}

