<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAmountAndInvoiceToWebUserSubscriptionTable extends Migration
{
    public function up()
    {
        Schema::table('web_user_subscription', function (Blueprint $table) {
            if (! Schema::hasColumn('web_user_subscription', 'amount')) {
                $table->decimal('amount', 12, 2)->nullable()->after('subscription_type');
            }
            if (! Schema::hasColumn('web_user_subscription', 'currency')) {
                $table->string('currency', 10)->nullable()->after('amount');
            }
            if (! Schema::hasColumn('web_user_subscription', 'invoice_path')) {
                $table->string('invoice_path', 255)->nullable()->after('currency');
            }
        });
    }

    public function down()
    {
        Schema::table('web_user_subscription', function (Blueprint $table) {
            if (Schema::hasColumn('web_user_subscription', 'invoice_path')) {
                $table->dropColumn('invoice_path');
            }
            if (Schema::hasColumn('web_user_subscription', 'currency')) {
                $table->dropColumn('currency');
            }
            if (Schema::hasColumn('web_user_subscription', 'amount')) {
                $table->dropColumn('amount');
            }
        });
    }
}
