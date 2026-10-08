<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddUniqueConstraintsToJobApplicationsTable extends Migration
{
    public function up()
    {
        // Normalize existing data (trim + lowercase email) so the
        // duplicate check and unique indexes behave consistently.
        $rows = DB::table('job_applications')->select('id', 'email', 'mobile')->get();
        foreach ($rows as $r) {
            $email = strtolower(trim((string) $r->email));
            $mobile = trim((string) $r->mobile);
            DB::table('job_applications')->where('id', $r->id)->update([
                'email' => $email,
                'mobile' => $mobile,
            ]);
        }

        // Remove duplicates, keeping the earliest application per (job, email)
        // and per (job, mobile).
        $dupEmails = DB::table('job_applications')
            ->select('posted_job_id', 'email', DB::raw('MIN(id) as keep_id'))
            ->groupBy('posted_job_id', 'email')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        foreach ($dupEmails as $d) {
            DB::table('job_applications')
                ->where('posted_job_id', $d->posted_job_id)
                ->where('email', $d->email)
                ->where('id', '<>', $d->keep_id)
                ->delete();
        }

        $dupMobiles = DB::table('job_applications')
            ->select('posted_job_id', 'mobile', DB::raw('MIN(id) as keep_id'))
            ->groupBy('posted_job_id', 'mobile')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        foreach ($dupMobiles as $d) {
            DB::table('job_applications')
                ->where('posted_job_id', $d->posted_job_id)
                ->where('mobile', $d->mobile)
                ->where('id', '<>', $d->keep_id)
                ->delete();
        }

        Schema::table('job_applications', function (Blueprint $table) {
            $table->unique(['posted_job_id', 'email'], 'job_applications_job_email_unique');
            $table->unique(['posted_job_id', 'mobile'], 'job_applications_job_mobile_unique');
        });
    }

    public function down()
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropUnique('job_applications_job_email_unique');
            $table->dropUnique('job_applications_job_mobile_unique');
        });
    }
}
