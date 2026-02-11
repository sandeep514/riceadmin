<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyQueryTable extends Migration
{
    public function up()
    {
        Schema::create('buy_query', function (Blueprint $table) {
            $table->id();
            $table->string('PackingType', 256);
            $table->string('mobile', 256);
            $table->string('partyName', 256);
            $table->string('portName', 256);
            $table->string('qualityName', 256);
            $table->string('quantity', 256);
            $table->text('remarks')->nullable();
            $table->string('qualityType', 256);
            $table->integer('validDays');
            $table->string('validDate', 256)->nullable();
            $table->integer('grade')->nullable();
            $table->integer('farming')->nullable();
            $table->integer('user');
            $table->string('length', 256)->nullable();
            $table->string('purity', 256)->nullable();
            $table->string('moisture', 256)->nullable();
            $table->string('broken', 256)->nullable();
            $table->string('kett', 256)->nullable();
            $table->string('dd', 256)->nullable();
            $table->integer('status')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('buy_query');
    }
}

