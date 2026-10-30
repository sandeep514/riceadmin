<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubscriptionTypeToAnalyserAccountsTable extends Migration
{
    public function up()
    {
        Schema::table('analyser_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('analyser_accounts', 'subscription_type')) {
                $table->string('subscription_type', 32)->nullable()->after('plan_id');
            }
        });
    }

    public function down()
    {
        Schema::table('analyser_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('analyser_accounts', 'subscription_type')) {
                $table->dropColumn('subscription_type');
            }
        });
    }
}
