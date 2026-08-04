<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleToLivePriceEventsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('live_price_events', 'title')) {
            Schema::table('live_price_events', function (Blueprint $table) {
                $table->string('title')->nullable()->after('quality_form_id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('live_price_events', 'title')) {
            Schema::table('live_price_events', function (Blueprint $table) {
                $table->dropColumn('title');
            });
        }
    }
}
