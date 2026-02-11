<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealLabReportsTable extends Migration
{
    public function up()
    {
        Schema::create('deal_lab_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sntc_no');
            $table->string('length', 191);
            $table->string('ad_mixture', 191);
            $table->string('sub_ad_mixture', 191)->nullable();
            $table->string('moisture', 191);
            $table->string('kett', 191)->nullable();
            $table->string('broken', 191);
            $table->string('dd', 191);
            $table->string('chalky', 191);
            $table->string('brown_layer', 191);
            $table->string('stone', 191);
            $table->string('inmature', 191);
            $table->string('broken_pin', 191);
            $table->string('cooking', 191);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('sntc_no');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deal_lab_reports');
    }
}

