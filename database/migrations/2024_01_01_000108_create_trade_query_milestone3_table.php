<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradeQueryMilestone3Table extends Migration
{
    public function up()
    {
        Schema::create('trade_query_milestone3', function (Blueprint $table) {
            $table->id();
            $table->integer('tradeFor')->default(1);
            $table->integer('queryId')->nullable();
            $table->integer('farmingType')->default(1);
            $table->integer('quality_type')->comment('basmati / non basmati');
            $table->integer('quality');
            $table->integer('qualityForm');
            $table->integer('qualityFormLinkWithLivePrice')->nullable();
            $table->string('stateLinkWithLivePrice', 255)->nullable();
            $table->integer('grade');
            $table->integer('packing');
            $table->string('quantity', 256);
            $table->string('offerPrice', 256);
            $table->dateTime('validDays');
            $table->text('packing_file')->nullable();
            $table->integer('packingStreamType')->nullable();
            $table->text('uncooked_file');
            $table->string('uncooked_file1', 256)->nullable();
            $table->string('uncooked_file2', 256)->nullable();
            $table->string('uncooked_file3', 256)->nullable();
            $table->text('cooked_file');
            $table->string('cooked_file1', 256)->nullable();
            $table->string('cooked_file2', 256)->nullable();
            $table->string('cooked_file3', 256)->nullable();
            $table->text('additioanlInfo')->nullable();
            $table->text('personal_remarks')->nullable();
            $table->string('location', 256)->nullable()->default('not disclosed');
            $table->string('crop', 256);
            $table->integer('hotdeal')->default(0);
            $table->integer('tradeType')->default(1);
            $table->string('moisture', 256)->nullable();
            $table->string('kett', 256)->nullable();
            $table->string('broken', 256)->nullable();
            $table->string('dd', 256)->nullable();
            $table->string('admixture', 256)->nullable();
            $table->string('elongation', 256)->nullable();
            $table->integer('riceSize')->nullable();
            $table->string('sntcLotNo', 255)->nullable();
            $table->integer('sold_at')->default(0);
            $table->integer('status')->default(1)->comment('3 => "sold", 2 => \'expired\' , 1 => \'Pending\',6=>\'Active\',4=>\'In-Process\',5=>\'De-active\',11 => \'close\', 12=> \'hold\'');
            $table->timestamp('created_at')->useCurrent();
            $table->integer('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('trade_query_milestone3');
    }
}

