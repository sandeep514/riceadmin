<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationAndEmploymentTypeToPostedJobsTable extends Migration
{
    public function up()
    {
        Schema::table('posted_jobs', function (Blueprint $table) {
            $table->string('location', 255)->nullable()->after('job_role');
            $table->string('employment_type', 32)->nullable()->after('location');
        });
    }

    public function down()
    {
        Schema::table('posted_jobs', function (Blueprint $table) {
            $table->dropColumn(['location', 'employment_type']);
        });
    }
}
