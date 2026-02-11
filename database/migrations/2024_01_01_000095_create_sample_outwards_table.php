<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSampleOutwardsTable extends Migration
{
    public function up()
    {
        Schema::create('sample_outwards', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('sntc_no', 191);
            $table->unsignedBigInteger('buyer');
            $table->unsignedBigInteger('quality');
            $table->string('bag_type', 191);
            $table->string('no_of_bags', 150);
            $table->string('qty_per_bag', 191)->nullable();
            $table->integer('qty')->nullable();
            $table->string('awb_no', 191);
            $table->text('photo')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('buyer');
            $table->index('quality');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sample_outwards');
    }
}

