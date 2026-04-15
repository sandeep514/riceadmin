<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePostedJobsRoleIdToJobRoleText extends Migration
{
    public function up()
    {
        Schema::table('posted_jobs', function (Blueprint $table) {
            $table->dropIndex(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::table('posted_jobs', function (Blueprint $table) {
            $table->string('job_role', 500)->nullable()->after('description');
        });
    }

    public function down()
    {
        Schema::table('posted_jobs', function (Blueprint $table) {
            $table->dropColumn('job_role');
        });

        Schema::table('posted_jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('description');
            $table->index('role_id');
        });
    }
}
