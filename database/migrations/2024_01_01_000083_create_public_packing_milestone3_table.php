<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicPackingMilestone3Table extends Migration
{
    public function up()
    {
        Schema::create('public_packing_milestone3', function (Blueprint $table) {
            $table->id();
            $table->string('size', 256);
            $table->string('packing', 256);
            $table->integer('order');
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('public_packing_milestone3');
    }
}

