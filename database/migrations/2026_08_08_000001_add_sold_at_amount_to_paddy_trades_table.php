<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoldAtAmountToPaddyTradesTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('paddy_trades', 'sold_at_amount')) {
            Schema::table('paddy_trades', function (Blueprint $table) {
                $table->string('sold_at_amount', 100)->nullable()->after('status');
            });
        }

        if (! Schema::hasColumn('paddy_trades', 'sold_at')) {
            Schema::table('paddy_trades', function (Blueprint $table) {
                $table->timestamp('sold_at')->nullable()->after('sold_at_amount');
            });
        }
    }

    public function down()
    {
        Schema::table('paddy_trades', function (Blueprint $table) {
            if (Schema::hasColumn('paddy_trades', 'sold_at')) {
                $table->dropColumn('sold_at');
            }
            if (Schema::hasColumn('paddy_trades', 'sold_at_amount')) {
                $table->dropColumn('sold_at_amount');
            }
        });
    }
}
