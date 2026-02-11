<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyQueryMilestone3Table extends Migration
{
    public function up()
    {
        Schema::create('buy_query_milestone3', function (Blueprint $table) {
            $table->id();
            $table->integer('quality_type')->comment('basmati , non basmati');
            $table->integer('quality');
            $table->integer('quality_form');
            $table->integer('grade');
            $table->integer('packing_type')->default(0);
            $table->integer('packing');
            $table->string('quantity', 256);
            $table->text('additional_info')->nullable();
            $table->text('remarks')->nullable();
            $table->string('farming', 256)->nullable();
            $table->string('contactPerson', 256)->nullable();
            $table->string('contactMobile', 256)->nullable();
            $table->string('type', 256)->default('app');
            $table->integer('status')->default(1);
            $table->integer('created_by');
            $table->timestamp('created_at')->useCurrent();
            $table->integer('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('buy_query_milestone3');
    }
}

