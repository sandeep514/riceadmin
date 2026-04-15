<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobApplicationsTable extends Migration
{
    public function up()
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('posted_job_id');
            $table->string('name');
            $table->string('email');
            $table->string('mobile', 64);
            $table->text('experience')->nullable();
            $table->string('cv_file', 500)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index('posted_job_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_applications');
    }
}
