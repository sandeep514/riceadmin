<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellQueryMilestone3Table extends Migration
{
    public function up()
    {
        Schema::create('sell_query_milestone3', function (Blueprint $table) {
            $table->id();
            $table->integer('quality_type')->comment('basmati / non basmati');
            $table->integer('quality');
            $table->integer('qualityForm');
            $table->integer('grade');
            $table->integer('packing');
            $table->string('quantity', 256);
            $table->string('offerPrice', 256);
            $table->string('validDays', 256);
            $table->text('packing_file')->nullable();
            $table->string('warehouselocation', 256);
            $table->text('uncooked_file');
            $table->text('cooked_file');
            $table->string('extra_file', 255)->nullable();
            $table->string('farming', 256)->nullable();
            $table->string('contactperson', 256);
            $table->string('contactMobile', 256);
            $table->text('remarks')->nullable();
            $table->integer('status')->default(1);
            $table->string('type', 255)->default('app');
            $table->integer('created_by');
            $table->timestamp('created_at')->useCurrent();
            $table->integer('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sell_query_milestone3');
    }
}

