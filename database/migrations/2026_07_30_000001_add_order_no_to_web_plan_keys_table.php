<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddOrderNoToWebPlanKeysTable extends Migration
{
    public function up()
    {
        Schema::table('web_plan_keys', function (Blueprint $table) {
            if (! Schema::hasColumn('web_plan_keys', 'order_no')) {
                $table->unsignedInteger('order_no')->nullable()->after('status');
            }
        });

        $rows = DB::table('web_plan_keys')->orderBy('id')->get(['id']);
        foreach ($rows as $index => $row) {
            DB::table('web_plan_keys')->where('id', $row->id)->update(['order_no' => $index + 1]);
        }
    }

    public function down()
    {
        Schema::table('web_plan_keys', function (Blueprint $table) {
            if (Schema::hasColumn('web_plan_keys', 'order_no')) {
                $table->dropColumn('order_no');
            }
        });
    }
}
