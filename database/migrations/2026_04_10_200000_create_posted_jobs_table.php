<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostedJobsTable extends Migration
{
    public function up()
    {
        Schema::create('posted_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->date('last_date_apply');
            $table->unsignedInteger('number_of_positions')->default(1);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index('role_id');
            $table->index('last_date_apply');
        });
    }

    public function down()
    {
        Schema::dropIfExists('posted_jobs');
    }
}
