<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlanIdToAnalyserAccountsTable extends Migration
{
    public function up()
    {
        Schema::table('analyser_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('analyser_accounts', 'plan_id')) {
                $table->unsignedBigInteger('plan_id')->nullable()->after('user_id');
                $table->index('plan_id');
            }
        });
    }

    public function down()
    {
        Schema::table('analyser_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('analyser_accounts', 'plan_id')) {
                $table->dropIndex(['plan_id']);
                $table->dropColumn('plan_id');
            }
        });
    }
}
